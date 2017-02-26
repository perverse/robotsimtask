<?php

namespace App\Console\Commands;

use App\Services\Contracts\SimulatorServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Validation\Factory as Validator;
use Illuminate\Support\MessageBag;

class RunSimulator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulator:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run coffee shop robot simulator.';

    /**
     * Validator factory for verifying CLI inputs
     *
     * @var string
     */
    protected $validator;

    /**
     * Our shop simulator service
     *
     * @var string
     */
    protected $sim;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Validator $validator, SimulatorServiceInterface $sim)
    {
        parent::__construct();

        $this->validator = $validator;
        $this->sim = $sim;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->welcomeMessage();
/*
        $shop_size = explode(" ", $this->getShopSize());
        $shop = [
            'width' => (int) $shop_size[0],
            'height' => (int) $shop_size[1]
        ];

        $num_robots = $this->getNumberRobots($shop);
        $shop['robots'] = $this->getRobots($num_robots, $shop);
*/
        $shop = [
            'width' => 5,
            'height' => 5,
            'robots' => [
                [
                    'x' => 3,
                    'y' => 3,
                    'heading' => 'S',
                    'commands' => 'LMM'
                ],
                [
                    'x' => 4,
                    'y' => 4,
                    'heading' => 'N',
                    'commands' => 'LMM'
                ]
            ]
        ];

        $new_shop = $this->sim->simulate($shop);
        
        $this->info("Final Shop State:");
        $this->info(json_encode($new_shop));
    }

    /**
     * Greet the user, we're not savages.
     *
     * @return mixed
     */
    public function welcomeMessage()
    {
        $this->info('');
        $this->info('Welcome to Nigels Coffee Shop Robot Controller Simulator!');
    }

    /**
     * A bit of sugar around our error messages. Again, not savages.
     *
     * @return mixed
     */
    public function throwErrors($messages)
    {
        if (is_array($messages)) {
            foreach ($messages as $text) {
                $this->throwError($text);
            }
        } else if ($messages instanceof MessageBag) {
            foreach ($messages->all() as $text) {
                $this->throwError($text);
            }
        } else {
            $this->throwError($messages);
        }
    }

    public function throwError($message)
    {
        $this->error("Sorry, there's been a problem... " . $message);
    }

    /**
     * Ask user for the size of the coffee shop.
     *
     * @return mixed
     */
    public function getShopSize()
    {
        $size = $this->ask('What is the size of the shop? [width:int height:int]');

        if (preg_match('/^[0-9]+ [0-9]+$/', $size)) {
            return $size;
        } else {
            $this->throwErrors('you have to enter the shop size in the format :int :int!');
            return $this->getGridSize();
        }
    }

    /**
     * Ask user how many robots they'd like.
     *
     * @return mixed
     */
    public function getNumberRobots($shop)
    {
        $grid_max = $shop['width'] * $shop['height'];
        $num_robots = (int) $this->ask(sprintf('How many robots would you like to deploy? [:int between 1 and %s]', $grid_max));

        if ($num_robots > 1 && $num_robots <= $grid_max) {
            return $num_robots;
        } else {
            $this->throwErrors(sprintf('the number of robots must be a positive integer between 1 and %s', $grid_max));
            return $this->getNumberRobots($grid_x, $grid_y);
        }
    }

    public function getRobots($num_robots, $shop)
    {
        $positions_taken_matrix = []; // matrix should be quicker than using something like in_array
        $robots = [];

        for ($i=0; $i<$num_robots; $i++) {
            $robots[] = $this->spawnRobot($i+1, $shop, $positions_taken_index);
        }

        return $robots;
    }

    public function spawnRobot($robot_num, $shop, &$index)
    {
        $index_valid = false;

        do {
            $new_robot = $this->getRobotPosition($robot_num, $shop);

            if (!isset($index[$new_robot['x'] . $new_robot['y']])) {
                // robot position not taken, we can accept this guy.
                $index[$new_robot['x'] . $new_robot['y']] = 1;
                $index_valid = true;
            }
        } while ($index_valid === false);

        $new_robot['commands'] = $this->getRobotCommands($robot_num);

        return $new_robot;
    }

    public function getRobotPosition($robot_num, $shop)
    {
        $position = $this->ask(sprintf('( Robot #%s ) What position would you like Robot #%s to start in? [xpos:int ypos:int heading:N,E,W,S]', $robot_num, $robot_num));
        $max_x = $shop['width'] - 1;
        $max_y = $shop['height'] - 1;

        if (preg_match('/^[0-9]+ [0-9]+ [NEWS]{1}$/', $position)) {
            $position_values = explode(" ", $position);
            $parsed_values = [
                'x' => (int) $position_values[0],
                'y' => (int) $position_values[1],
                'heading' => $position_values[2]
            ];

            $validator = $this->validator->make($parsed_values, [
                'x' => sprintf("numeric|between:0,%s", $max_x),
                'y' => sprintf("numeric|between:0,%s", $max_y)
            ]);

            if ($validator->passes()) {
                return $parsed_values;
            } else {
                $this->throwErrors($validator->errors());
            }

        } else {
            $this->throwErrors("the position input must be in the format [xpos:int ypos:int heading:N,E,W,S]");
        }

        return $this->getRobotPosition($robot_num, $shop);
    }

    public function getRobotCommands($robot_num)
    {
        $commands = $this->ask(sprintf('( Robot #%s ) Please enter movement commands for Robot #%s', $robot_num, $robot_num));

        if (preg_match('/^[LRM]+$/', $commands)) {
            return $commands;
        } else {
            $this->throwErrors("robot movement commands can only consist of a single string (no spaces) containing the characters 'L' (turn left), 'R' (turn right) and 'M' (move forward)");
            return $this->getRobotCommands($robot_num);
        }
    }
}
