# Robot Controller Simulator Coding Task
[![Build Status](https://travis-ci.org/perverse/robotsimtask.svg?branch=master)](https://travis-ci.org/perverse/robotsimtask)

This is a Laravel App designed to meet the business requirements of a coding task. The following readme is written for linux (and somewhat OSX at a stretch), Windows users will have to extrapolate - sorry!

## Modules

As with most web projects, I stand on the shoulders of giants. Outside of Laravel, I used the following PHP modules in my build (and props to the authors):

* bosnadev/repositories - https://github.com/bosnadev/repository - Fantastic repository pattern wrapper for Eloquent.
* coduo/php-humanizer - https://github.com/coduo/php-humanizer - Excellent little string/number/other formatting library.
* jenssegers/mongodb - https://github.com/jenssegers/laravel-mongodb - Eloquent driver for MongoDB.
* league/fractal - http://fractal.thephpleague.com/ - Insane library for consistent formatting of data from API's. Provides my transform layer, I really really love this lib.

## Dependencies

* PHP 7.0+ - Built and tested using PHP 7.0. This is so high to satisfy the requirement of the mongodb laravel driver - which leverages the new PHP MongoDB driver.
* MongoDB PHP Driver

## Pre-Installation

* You should have a MongoDB database set up with an associated user that has full read/write access to the db
* You should have a PHP-compatible webserver configured and setup (nginx, apache2, etc)
* You should have composer setup and configured on your workstation

## Installation

* Step 1 - Clone this repository to a directory
> git clone https://github.com/perverse/robotsimtask.git /path/to/directory

* Step 2 - Navigate to directory and composer install
> cd /path/to/directory && composer install

* Step 3 - Copy .env.example to .env and update file as necessary with your configuration (see Dotenv Configuration heading below for descriptions of each of the configuration options). Only the Database options are critical, and they should be directed to your pre-installed MongoDB.
> cp .env.example .env && nano .env

* Step 4 - Configure your webserver to point to repository_root/public - ensure webserver configuration is laravel compatible.

## Dotenv Configuration

* **DB_HOST** - IP or Hostname of your MongoDB database server. [Defaults to 'localhost']
* **DB_PORT** - Port of your MongoDB database server. [Defaults to '27017']
* **DB_DATABASE** - Database name of your MongoDB database. [Defaults to 'test']
* **DB_USER** - Username of the MongoDB user that has permission to the MONGO_DB_NAME database. Can be left blank if Mongo isn't configured for auth.
* **DB_PASS** - Corresponding password for your MongoDB user. Should be left blank is MONGO_DB_USER is left blank.

## Usage - CLI

Once you have installed and configured the app, you can access the simulators CLI interface using the following command from the root repository directory:

> php artisan simulator:run

You will then be guided by the prompts

## Usage - API

The API interface can be accessed as per specification at the url: http://vhost.url/api/endpoint. A brief summary of the endpoints is below:

### POST /api/shop
#### Example Request
```
POST /api/shop HTTP/1.1
Host: vhost.url
Content-Type: application/x-www-form-urlencoded
Cache-Control: no-cache

width=20&height=10
```
#### Example Response
```
{
    "result": {
        "id": "58b3942391d5ef7de425e7e2",
        "width": "20",
        "height": "10"
    },
    "status": "ok"
}
```

### GET /api/shop/:id
#### Example Request
```
GET /api/shop/58b3942391d5ef7de425e7e2 HTTP/1.1
Host: vhost.url
Content-Type: application/x-www-form-urlencoded
Cache-Control: no-cache
```
#### Example Response
```
{
    "result": {
        "id": "58b3942391d5ef7de425e7e2",
        "width": "20",
        "height": "10",
        "robots": [
            {
                "x": 5,
                "y": 5,
                "heading": "N",
                "commands": "LMMMM"
            }
        ]
    },
    "status": "ok"
}
```

### DELETE /api/shop/:id
#### Example Request
```
DELETE /api/shop/58b3942391d5ef7de425e7e2 HTTP/1.1
Host: vhost.url
Content-Type: application/x-www-form-urlencoded
Cache-Control: no-cache
```
#### Example Response
```
{
    "status": "ok"
}
```

### POST /api/shop/:id/robot
#### Example Request
```
POST /api/shop/58b3942391d5ef7de425e7e2/robot HTTP/1.1
Host: vhost.url
Content-Type: application/x-www-form-urlencoded
Cache-Control: no-cache

x=5&y=5&heading=N@commands=LMM
```
#### Example Response
```
{
    "result": {
        "id": "58b395e791d5ef7df025c262",
        "x": "5",
        "y": "5",
        "heading": "N",
        "commands": "LMM"
    },
    "status": "ok"
}
```

### PUT /api/shop/:id/robot/:rid
#### Example Request
```
PUT /api/shop/58b3942391d5ef7de425e7e2/robot/58b395e791d5ef7df025c262 HTTP/1.1
Host: vhost.url
Content-Type: application/x-www-form-urlencoded
Cache-Control: no-cache

x=6&y=6&heading=S@commands=RLRLRMMMM
```
#### Example Response
```
{
    "result": {
        "id": "58b395e791d5ef7df025c262",
        "x": "6",
        "y": "6",
        "heading": "S",
        "commands": "RLRLRMMMM"
    },
    "status": "ok"
}
```

### DELETE /api/shop/:id/robot/:rid
#### Example Request
```
DELETE /api/shop/58b3942391d5ef7de425e7e2/robot/58b395e791d5ef7df025c262 HTTP/1.1
Host: vhost.url
Content-Type: application/x-www-form-urlencoded
Cache-Control: no-cache
```
#### Example Response
```
{
    "status": "ok"
}
```

### POST /api/shop/:id/execute
#### Example Request
```
POST /api/shop/58b3942391d5ef7de425e7e2/execute HTTP/1.1
Host: vhost.url
Content-Type: application/x-www-form-urlencoded
Cache-Control: no-cache
```
#### Example Response
```
{
    "result": {
        "id": "58b397be91d5ef7df15ced22",
        "width": "20",
        "height": "10",
        "robots": [
            {
                "id": "58b3986391d5ef7de3763672",
                "x": 0,
                "y": 3,
                "heading": "S",
                "commands": "LMMM"
            },
            {
                "id": "58b3987491d5ef7df025c263",
                "x": 8,
                "y": 2,
                "heading": "N",
                "commands": "LMMM"
            }
        ]
    },
    "status": "ok"
}
```

## Project Thoughts/Musings

* I had to make a few assumptions to complete the task:
  * Robots *can* move into a spot that's about to be vacated - as the robots move in parallel, but cannot move into a spot that will be vacated - or that is occupied by a robot heading directly towards it (as they would have to move through each other). All other movement is permitted except;
  * Robots, when faced with a boundary/wall, will stop moving and wait for their next command that doesn't move them into a wall
* The ApiResponse pattern is of my own design. It's a work in progress, still rough around the edges, but if you want to look at the inner workings - the files are:
  * App\Services\ApiResponseFormatter - this is the main service that does the bulk of the work.
  * App\Containers\ApiResponse - This container object is returned by all methods of API-facing services. Gives a common container for formatting different interfaces.
  * App\Http\Middleware\ApiResponseJson - "After" middleware that catches ApiResponse objects and formats them to JSON using ApiResponseFormatter
* I chose MongoDB as my data store because the nature of the data lent itself to a single document, and the overhead of handling certain robot lookup operations on the PHP side should be well made up for by the quick lookups and updates of Mongo at scale.
* I have clear separation of concerns - the Controller layer purely pipes data to services -> service layer is business logic layer -> repository layer is data logic. If it was nevessary to move back to an RDB, you'd only need to make some minor changes to the repositories and models.
* Mongo caused more issues than it was probably worth for this exercise, especially with testing.

## License

The MIT License (MIT)

Copyright (c) 2017 Ronnie Pyne

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.