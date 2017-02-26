<?php

namespace App\Services;

use App\Exceptions\Simulator\RobotCollisionException;
use App\Services\Contracts\SimulatorServiceInterface;
use Illuminate\Support\Collection;

class SimulatorService implements SimulatorServiceInterface
{
    protected $compass = [
        0 => 'N',
        1 => 'E',
        2 => 'S',
        3 => 'W'
    ];

    protected $inverse_compass = [];
    protected $compass_opposites = [
        'N' => 'S',
        'E' => 'W',
        'S' => 'N',
        'W' => 'E'
    ];

    protected $grid = [
        'x' => [
            'min' => 0,
            'max' => 0
        ],
        'y' => [
            'min' => 0,
            'max' => 0
        ]
    ];

    protected function getMaxPasses(Collection $robots)
    {
        return $robots->map(function($robot, $key){
            return strlen(array_get($robot, 'commands', ''));
        })->max();
    }

    protected function setup($shop)
    {
        $this->setupGrid($shop);
        $this->setupCompass();
    }

    protected function setupGrid($shop)
    {
        $this->grid = [
            'x' => [
                'min' => 0,
                'max' => $shop['width'] - 1
            ],
            'y' => [
                'min' => 0,
                'max' => $shop['height'] - 1
            ]
        ];
    }

    protected function setupCompass()
    {
        $this->inverse_compass = array_flip($this->compass);
    }

    public function simulate(array $shop)
    {
        $this->setup($shop);
        $robots = collect($shop['robots']);

        $max_passes = $this->getMaxPasses($robots);
        $sim = $this;

        // our main loop - loops for maximum passes
        
        for ($pass=1; $pass <= $max_passes; $pass++) {
            $new_positions = collect();

            $robots->each(function($robot, $key) use (&$new_positions, &$sim, &$robots, $pass) {
                $command = $sim->getRobotCommand($robot, $pass);

                if ($command == 'M') {
                    // robot plans to move, get orientation and check if path is and will be clear as far as we know
                    // we only need to check our "new_positions" for this, as well as checking to see if a robot is
                    // coming directly at us, as that collision type won't come up in checking the future positions

                    $planned_position = $sim->getNewRobotPosition($robot, $command);

                    // check easy collisions
                    if ($new_positions->contains(function($compare, $key) use (&$planned_position){
                        return $compare['x'] == $planned_position['x'] && $compare['y'] == $planned_position['y'];
                    })) {
                        // 2 robots moving to same space, throw exception
                        throw new RobotCollisionException(sprintf("Robot Collision Detected @ x: %s, y: %s, pass: %s", $planned_position['x'], $planned_position['y'], $pass));
                    }

                    // check head-on collision
                    $potential_collision = $planned_position;
                    $potential_collision['heading'] = $sim->getOppositeHeading($robot['heading']);

                    // check last pass robots for head-on robots
                    if ($robots->contains(function($compare, $value) use (&$potential_collision){
                        return $compare['x'] == $potential_collision['x']
                            && $compare['y'] == $potential_collision['y']
                            && $compare['heading'] == $potential_collision['heading'];
                    })) {
                        throw new RobotCollisionException(sprintf("Robot Collision Detected @ x: %s, y, %s, pass %s", $potential_collision['x'], $potential_collision['y'], $pass));
                    }

                    // no collisions detected, queue movement command
                    $new_positions->push($planned_position);
                } else if ($command) {
                    // robot just needs to spin around
                    $new_positions->push($this->getNewRobotPosition($robot, $this->getRobotCommand($robot, $pass)));
                } else {
                    // robot doesn't have anymore commands after this many passes, does nothing
                    $new_positions->push($robot);
                }
            });
            echo "pass: $pass";
            print_r($new_positions->all());
            $robots = $new_positions;
            // if you wanted to use this platform as the actual robot controller, you could queue up the $robots collection at this point
        }

        $shop['robots'] = $robots->all();

        return $shop;
    }

    public function getRobotCommand($robot, $pass)
    {
        return substr($robot['commands'], $pass - 1, 1);
    }

    public function getNewRobotPosition($robot, $command)
    {
        $sim = $this;

        $move_map = [
            'N' => function($robot) use (&$sim){
                $new_y = $robot['y'] - 1;

                if ($new_y >= $sim->grid['y']['min']) {
                    $robot['y'] = $new_y;
                }

                return $robot;
            },
            'E' => function($robot) use (&$sim){
                $new_x = $robot['x'] + 1;

                if ($new_x <= $sim->grid['x']['max']) {
                    $robot['x'] = $new_x;
                }

                echo "new robot: " . print_r($robot, true);

                return $robot;
            },
            'S' => function($robot) use (&$sim){
                $new_y = $robot['y'] + 1;

                if ($new_y <= $sim->grid['y']['max']) {
                    $robot['y'] = $new_y;
                }

                return $robot;
            },
            'W' => function($robot) use (&$sim){
                $new_x = $robot['x'] - 1;

                if ($new_x >= $sim->grid['x']['min']) {
                    $robot['x'] = $new_x;
                }

                return $robot;
            }
        ];

        $heading_map = [
            'L' => function($robot) use (&$sim) {
                $new_heading = $this->inverse_compass[$robot['heading']] - 1;
                if ($new_heading < 0) $new_heading = 3;
                $robot['heading'] = $this->compass[$new_heading];

                return $robot;
            },
            'R' => function($robot) use (&$sim) {
                $new_heading = $this->inverse_compass[$robot['heading']] + 1;
                if ($new_heading > 3) $new_heading = 0;
                $robot['heading'] = $this->compass[$new_heading];

                return $robot;
            }
        ];

        if ($command == 'M') {
            return $move_map[$robot['heading']]($robot);
        } else {
            return $heading_map[$command]($robot);
        }
    }

    protected function getOppositeHeading($heading)
    {
        return $this->compass_opposites[$heading];
    }
}