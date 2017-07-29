# Developing

## Configuring your workspace

1. Install [composer](https://getcomposer.org/download/) and
	[npm](https://www.npmjs.com/get-npm).
2. `composer install` and `npm install` in this directory to get necessary dev
	dependencies.

At this point, you can lint your code by running the `bin/phpcs.sh` file from
this directory.

### In-editor linting (Sublime)

If you would like to lint automatically in Sublime 3:

1. Install (via Package Control) `SublimeLinter` and `SublimeLinter-phpcs`.
2. Install global PHPCS using composer (`composer global require "squizlabs/php_codesniffer=*"`)
3. Ensure your composer directory (generally `~/.composer/vendor/bin`) is in `$PATH`.
4. Add some configuration to Sublime's `SublimeLinter.sublime-settings`:
	```
	"chdir": "${project}",
	"cmd": "vendor/bin/phpcs"
	"standard": [
        "${project}/phpcs.xml",
        "${directory}/phpcs.xml",
        "${project}/phpcs.ruleset.xml",
        "${directory}/phpcs.ruleset.xml",
    ]
    ```

And linting should now work, in-editor.

## Run tests

1. Install [PHPUnit](https://phpunit.de/manual/current/en/installation.html)
2. Setup a dev SQL server (perhaps using [VVV](https://varyingvagrantvagrants.org/))
3. `bin/install-wp-tests.sh wptest db_user db_pass db_location` (where `wptest`
	can be whatever DB name you want). User, pass, and location are
	`root`, `password`, and `localhost` on my box respectively.

## Usage

You can run tests at any time using the `phpunit` command.

You can lint at any time running via `bash bin/phpcs.sh` (or `grunt phpcs`).
