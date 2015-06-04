# Lorry

A web platform to host and showcase released Clonk addons and their required packages.

## Deploying

Lorry can be easily deployed with [Capistrano](http://capistranorb.com/).

### Requirements

Your server(s) require [PHP](http://php.net/), [Composer](http://getcomposer.org/), an sql-backed database supported by [Doctrine](https://doctrine-dbal.readthedocs.org/en/latest/reference/configuration.html#driver) and [Redis](http://redis.io/).

### Prepare your deployment system

*The deployment system is the machine, you'll be running your deployments from. It will connect to your webserver(s) and deploy Lorry onto them.*

First, clone this repository to your deployment system. It contains all the files required to deploy Lorry.

[Install Capistrano](http://capistranorb.com/documentation/getting-started/installation/) and [Bundler](http://bundler.io/) on your deployment system (you may have to install [Ruby](https://www.ruby-lang.org/) first).

Execute `bundle` in the root of the cloned repository. It should install all the gems necessary for deployment.

### Set up the stages

Each deployment stage (such as testing, staging, production) requires a configuration file in `config/deploy`, for example `config/deploy/production.rb`.

The file should contain the target server(s), folders and any other options:

```ruby
# Target server(s)
server "example.com", user: "deploy", roles: %w{app}

# Target directory
set :deploy_to, '/var/www/lorry'

# PATH should contain php and composer
set :default_env, { path: "/usr/bin:$HOME/bin:$PATH" }
```

### Configure your webserver

Configure your webserver so it points incoming requests to `<deploy_to>/current/web` (<deploy_to> being the target directory from the previous step).

### Configure Lorry

Execute `cap <stagename> config:init` in the repository root. It should copy the example files and inform you where you can find the created files.
Edit the configuration files according to their documentation.

### Push to server(s)

You are now ready to deploy Lorry on your server(s): `cap <stagename> deploy`.

### Run the worker(s)

The platform uses workers for asynchronous handling of various tasks (such as sending mails or publishing releases).
These workers are backed by a [PHP port](https://github.com/vend/php-resque) of [Resque](https://github.com/resque/resque) running on Redis.

To launch a worker for all tasks, execute `php <deploy_to>/current/app/worker worker --queue=*` (remember to escape the * in your shell if necessary).
Remember to restart any workers after every deployment, so that they don't run outdated code.

## Licensing

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
