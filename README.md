# Lorry

A web platform to host and showcase released Clonk addons and their required packages.

## Deploying

Lorry can be easily deployed with [Capistrano](http://capistranorb.com/).

### Prepare your deployment system

First, clone this repository to your deployment system. It contains all the files required for Lorry to run.

[Install Capistrano](http://capistranorb.com/documentation/getting-started/installation/) and [Bundler](http://bundler.io/) on your deployment system (you may have to install [Ruby](https://www.ruby-lang.org/) first).

Execute `bundle` in the root of the cloned repository. It should install all the gems necessary for deployment.

### Installing the servers

Your servers require [PHP](http://php.net/), an installed version of [Composer](http://getcomposer.org/), a database set up for PHP's PDO and [Redis](http://redis.io/).
Choose a folder where Capistrano should deploy to and configure your webserver to point incoming requests to `<deployfolder>/current/web`.

### Setting up your stages

Each deployment stage (such as testing, staging, production) requires a configuration file in `config/deploy`, for example `config/deploy/production.rb`.

The file should contain the target server(s), folders and other options:

```ruby
# Target server(s)
server "example.com", user: "deploy", roles: %w{app}

# Target directory
set :deploy_to, '/var/www/lorry'

# PATH should contain php and composer
set :default_env, { path: "/usr/bin:$HOME/bin:$PATH" }
```

### Configuring Lorry

Execute `cap <stagename> config:init` in the repository root. It should copy the example files and inform you where you can find the created files.
Edit the configuration files according to their documentation.

### Push to server

You are now ready to deploy Lorry on your server(s): `cap <stagename> deploy`.

## Running the workers

The platform uses workers for asynchronous handling of various tasks (such as sending mails or publishing releases).
These workers are backed by a [PHP port](https://github.com/chrisboulton/php-resque) of [Resque](https://github.com/resque/resque) running on Redis.

php-resque provides an executable file at `vendor/bin/resque`. You can invoke it like this: `QUEUE=* APP_INCLUDE=<deploydir>/current/app/bootstrap.php php bin/resque`.
See the [php-resque documentation on workers](https://github.com/chrisboulton/php-resque#workers) for additional parameters.

Remember to restart the worker after every deployment.

## Copying

Copyright (C) 2013-2015  Benedict Etzel

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published
by the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.