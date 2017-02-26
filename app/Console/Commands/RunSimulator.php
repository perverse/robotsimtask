<?php

namespace App\Console\Commands;

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Validator $validator)
    {
        parent::__construct();

        $this->validator = $validator;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->welcomeMessage();
        $shop_size = explode(" ", $this->getShopSize());
        $shop = [
            'width' => (int) $shop_size[0],
            'height' => (int) $shop_size[1]
        ];

        $num_robots = $this->getNumberRobots($shop);
        $robot_commands = $this->getRobots($num_robots, $shop);
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
        $this->info('');
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

        for ($i=0; $i<$num_robots; $i++) {
            $this->robots[] = $this->spawnRobot($i+1, $shop, $positions_taken_matrix);
        }
    }

    public function spawnRobot($robot_num, $shop, &$matrix)
    {
        $matrix_valid = false;

        do {
            $new_robot = $this->getRobotPosition($robot_num, $shop);

            if (!isset($matrix[$new_robot['x']][$new_robot['y']])) {
                // robot position not taken, we can accept this guy.
                $matrix[$new_robot['x']][$new_robot['y']] = 1;
                $matrix_valid = true;
            }
        } while ($matrix_valid === false);

        $robot['commands'] = $this->getRobotCommands();

        return $robot;
    }

    public function getRobotPosition($robot_num, $shop)
    {
        $position = $this->ask(sprintf('(Robot %s) What position would you like this robot to start in? [xpos:int ypos:int heading:N,E,W,S]', $robot_num));
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

        return $this->spawnRobot($robot_num, $shop);
    }

    public function getRobotCommands()
    {

    }
}
