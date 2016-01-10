# img.pew.cc

img.pew.cc is a small website for simple image hosting. See [img.pew.cc](http://img.pew.cc)

## Setup

* Use [composer](https://getcomposer.org/) to install the dependencies: `composer install`
* Create `Application/localconfig.php` and edit the config values from  `Application/config.php` there.
* Use `data/schema.sql` to create a sqlite3 database `sqlite3 data/db.sqlite3 < data/schema.sql`
* Point the web server root directory to `public/`

## Development

* Use [npm](https://nodejs.org/) to install the dev dependencies: `npm install`
* Use [grunt](http://gruntjs.com/) to build the code: `grunt build`
  * Grunt calls composer to get the php dependencies. Make sure the composer binary is globally installed and named `composer`
* After modifying the less or JavaScript code, call `grunt build` again to compile it

