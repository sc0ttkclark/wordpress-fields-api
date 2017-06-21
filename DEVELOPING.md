# Developing

## Configuring your workspace (Manual)

1. Install [composer](https://getcomposer.org/download/) and
	[npm](https://www.npmjs.com/get-npm).
2. `composer install` and `npm install` in this directory to get necessary dev
	dependencies.

At this point, you can lint your code by running the `bin/phpcs.sh` file from
this directory. 

## Run tests

1. Install [PHPUnit](https://phpunit.de/manual/current/en/installation.html)
2. Setup a dev SQL server
3. `bin/install-wp-tests.sh wptest db_user db_pass db_location` (where `wptest`
	can be whatever DB name you want). User, pass, and location are
	`root`, `password`, and `localhost` on my box respectively.

## Usage

You can run tests at any time using the `phpunit` command.

You can lint at any time running via `bash bin/phpcs.sh` (or `grunt phpcs`).
