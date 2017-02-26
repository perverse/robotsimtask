<?php

namespace App\Containers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\AbstractPaginator;
use League\Fractal\TransformerAbstract as Transformer;

class ApiResponse
{
    protected $http_response_code = null;
    protected $errors = array();
    protected $data = null;
    protected $data_type = null;
    protected $messages = array();
    protected $success = false;
    protected $error_type = null;
    protected $original = null;
    protected $transformer = null;
    protected $result_index = null;

    // ERROR TYPES
    const ERROR_TYPE_MISC = 1;
    const ERROR_TYPE_VALIDATION = 2;
    const ERROR_TYPE_AUTH = 3;
    const ERROR_TYPE_NOT_FOUND = 4;

    // DATA TYPES
    const DATA_ITEM = 1;
    const DATA_COLLECTION = 2;
    const DATA_PAGINATION = 3;

    public function make(array $args)
    {
        $response = new self;

        $response_code = array_get($args, 'http_response_code', null);
        $errors = array_get($args, 'errors', array());
        $data = array_get($args, 'data', null);
        $messages = array_get($args, 'messages', array());
        $success = array_get($args, 'success', false);
        $error_type = array_get($args, 'error_type', null);
        $data_type = array_get($args, 'data_type', null);
        $transformer = array_get($args, 'transformer', null);
        $response_index = array_get($args, 'result_index', 'result');

        $response->setSuccess($success)
                 ->setErrors($errors)
                 ->setHttpResponseCode($response_code)
                 ->setData($data)
                 ->setMessages($messages)
                 ->setErrorType($error_type)
                 ->setDataType($data_type)
                 ->setTransformer($transformer)
                 ->setResultIndex($response_index);

        return $response;
    }

    public function setErrorType($error_type)
    {
        $this->error_type = $error_type;
        return $this;
    }

    public function getErrorType()
    {
        return $this->error_type;
    }

    public function setHttpResponseCode($code)
    {
        $this->http_response_code = $code;
        return $this;
    }

    public function getHttpResponseCode()
    {
        if (!is_null($this->http_response_code))
            return $this->http_response_code;

        if ($this->getSuccess())
            return 200;

        switch ($this->error_type) {
            case self::ERROR_TYPE_MISC:
            default:
                return 500;
                break;
            case self::ERROR_TYPE_VALIDATION:
                return 400;
                break;
            case self::ERROR_TYPE_NOT_FOUND:
                return 404;
                break;
            case self::ERROR_TYPE_AUTH:
                return 403;
                break;
        }
    }

    public function setSuccess($success = true)
    {
        $this->success = $success;
        return $this;
    }

    public function getSuccess()
    {
        return ($this->success) ? true : false;
    }

    public function setData($data)
    {
        $this->original = $data;

        if ($data instanceof AbstractPaginator) {
            $this->data = $data->toArray();
            $this->setDataType(self::DATA_PAGINATION);
        } else if ($data instanceof Collection) {
            $this->data = $data;
            $this->setDataType(self::DATA_COLLECTION);
        } else if ($data instanceof Model) {
            $this->data = $data;
            $this->setDataType(self::DATA_ITEM);
        } else {
            $this->data = $data;
        }

        return $this;
    }

    public function getData($index = null)
    {
        if ($index !== null) {
            return array_get($this->data, $index, null);
        } else {
            return $this->data;
        }
    }

    public function getOriginalData()
    {
        return $this->original;
    }

    public function setDataType($type)
    {
        if ($type !== null) {
            $this->data_type = $type;
        }
        return $this;
    }

    public function getDataType()
    {
        if (empty($this->data)) return false;
        return $this->data_type;
    }

    public function setMessages(array $messages)
    {
        $this->messages = $messages;
        return $this;
    }

    public function getMessages()
    {
        if (is_array($this->messages)) {
            $ret = $this->messages;
        } else if ($this->messages instanceof Illuminate\Support\MessageBag) {
            $ret = $this->messages->getMessages();
        }

        return $this->flatten($ret);
    }

    public function setErrors(array $errors)
    {
        $this->errors = $errors;
        return $this;
    }

    public function getErrors()
    {
        if (is_array($this->errors)) {
            $ret = $this->errors;
        } else if ($this->errors instanceof Illuminate\Support\MessageBag) {
            $ret = $this->errors->getMessages();
        }

        return $this->flatten($ret);
    }

    public function setTransformer($transformer)
    {
        $this->transformer = $transformer;
        return $this;
    }

    public function getTransformer()
    {
        return $this->transformer;
    }

    public function setResultIndex($index)
    {
        $this->result_index = $index;
        return $this;
    }

    public function getResultIndex()
    {
        return $this->result_index;
    }

    protected function flatten($array, $prefix = '')
    {
        $result = array();

        foreach($array as $key => $value) {
            if(is_array($value)) {
                $result = $result + $this->flatten($value, $prefix . $key . '.');
            } else {
                $result[$prefix . $key] = $value;
            }
        }

        return $result;
    }

    public function success($args = null)
    {
        if (!is_array($args) && $args !== null) {
            $args = ['data' => $args];
        }

        $full_args = array_merge(['success' => true], $args);
        return $this->make($full_args);
    }

    public function error($args = null)
    {
        if (!is_array($args) && $args !== null) {
            $args = ['data' => $args];
        }

        return $this->make(array_merge(['success' => false], $args));
    }

    public function get($index = null)
    {
        return $this->getData($index);
    }

    public function __toString()
    {
        return get_class($this); // avoids errors with Symfonys response builders and constructors... is never actually used in laravel land
    }
}