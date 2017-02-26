<?php

namespace App\Services;

use App\Containers\ApiResponse as ServiceResponse;
use App\Fractal\Paginator\IlluminateSimplePaginatorAdapter;
use League\Fractal\Manager as Fractal;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\Collection as FractalCollection;
use Illuminate\Contracts\Container\Container as ServiceContainer;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use App\Exceptions\ServiceResponseException;

class ApiResponseFormatter
{
    protected $service_response;

    public function __construct(ServiceContainer $app, Fractal $fractal)
    {
        $this->app = $app;
        $this->fractal = $fractal;

        $this->fractal->setSerializer(new \App\Fractal\Serializers\CustomDataArraySerializer);
    }

    public function make(ServiceResponse $service_response)
    {
        $formatter = $this->app->make(self::class);
        $formatter->setServiceResponse($service_response);

        return $formatter;
    }

    public function setServiceResponse(ServiceResponse $service_response)
    {
        $this->service_response = $service_response;

        return $this;
    }

    public function getServiceResponse()
    {
        return $this->service_response;
    }

    protected function getLoadedRelationshipsRecursive($model, $loaded_relations, $return_array=[], $parent_nesting='')
    {
        foreach ($loaded_relations as $relationship) {
            $related_model = $model->$relationship;

            if ($related_model instanceof EloquentCollection) {
                $related_model = $related_model->first();
            }

            if ($related_model) {
                $nested_relationships = $related_model->getLoadedRelationships();
                $delete_index = array_search('pivot', $nested_relationships);

                if ($delete_index !== false) {
                    unset($nested_relationships[$delete_index]);
                }

                if (!empty($nested_relationships)) {
                    if (!empty($parent_nesting)) { 
                        $new_nesting = $parent_nesting . '.' . $relationship;
                    } else {
                        $new_nesting = $relationship;
                    }

                    $return_array = $this->getLoadedRelationshipsRecursive($related_model, $nested_relationships, $return_array, $new_nesting);
                } else {
                    if (!empty($parent_nesting)) { 
                        $return_array[] = $parent_nesting . '.' . $relationship;
                    } else {
                        $return_array[] = $relationship;
                    }
                }
            }
        }

        return $return_array;
    }

    protected function getTransformer()
    {
        if ($transformer = $this->service_response->getTransformer()) {
            return $transformer;
        } else {
            $full_class_name = false;
            $loaded_relations = [];

            if ($this->service_response->getDataType() == ServiceResponse::DATA_ITEM) {
                $model = $this->service_response->getOriginalData();
                $full_class_name = get_class($model);

                $loaded_relations = $model->getLoadedRelationships();
            } else if ($this->service_response->getDataType() == ServiceResponse::DATA_COLLECTION) {
                $first = $this->service_response->getOriginalData()->first();

                if ($first) {
                    $full_class_name = get_class($first);
                    $loaded_relations = $first->getLoadedRelationships();
                }
                $model = $first;
            } else if ($this->service_response->getDataType() == ServiceResponse::DATA_PAGINATION) {
                $first = $this->service_response->getOriginalData()->getCollection()->first();

                if ($first) {
                    $full_class_name = get_class($first);
                    $loaded_relations = $first->getLoadedRelationships();
                }

                $model = $first;
            }

            if (!empty($loaded_relations)) {
                $loaded_relations = $this->getLoadedRelationshipsRecursive($model, $loaded_relations);
            }

            if ($full_class_name) {
                $short_class_name = join('', array_slice(explode('\\', $full_class_name), -1));
                $transformer = $this->app->make("App\Transformers\\" . $short_class_name . "Transformer");

                if ($transformer) {
                    if (!empty($loaded_relations)) {
                        $this->fractal->parseIncludes($loaded_relations);
                    }
                    return $transformer;
                }
            }
        }

        // Couldn't find a transformer after all that, throw an error! we need one!
        // throw new ServiceResponseException('Could not find Transformer class for payload: <br><pre>' . print_r($this->service_response->getOriginalData(), true));

        // Fallback to a standard/default transformer.
        return $this->app->make("App\Transformers\FallbackTransformer");
    }

    protected function organiseFractalData($result)
    {
        $result[$this->service_response->getResultIndex()] = $result['data'];
        unset($result['data']);

        return $result;
    }

    protected function paginatorData()
    {
        $resource = new FractalCollection($this->service_response->getOriginalData(), $this->getTransformer(), 'result');
        $resource->setPaginator(new IlluminateSimplePaginatorAdapter($this->service_response->getOriginalData()));

        $result = $this->fractal->createData($resource)->toArray();

        return $result;
    }

    protected function collectionData()
    {
        $resource = new FractalCollection($this->service_response->getOriginalData(), $this->getTransformer(), 'result');
        $result = $this->fractal->createData($resource)->toArray();

        return $result;
    }

    protected function itemData()
    {
        $resource = new FractalItem($this->service_response->getOriginalData(), $this->getTransformer(), 'result');
        $result = $this->fractal->createData($resource)->toArray();

        return $result;
    }

    public function toJsonResponse()
    {
        if ($this->service_response->getSuccess()) {
            switch ($this->service_response->getDataType()) {
                case ServiceResponse::DATA_ITEM:
                    $ret = $this->itemData();
                    break;
                case ServiceResponse::DATA_COLLECTION:
                    $ret = $this->collectionData();
                    break;
                case ServiceResponse::DATA_PAGINATION:
                    $ret = $this->paginatorData();
                    break;
                default:
                    $ret = [];
                    break;
            }

            $ret['messages'] = array_values($this->service_response->getMessages());
        } else {
            $ret['errors'] = array_values($this->service_response->getErrors());
        }

        $ret['status'] = $this->service_response->getSuccess();

        return response()->json($ret, $this->service_response->getHttpResponseCode());
    }
}