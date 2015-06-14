# config valid only for current version of Capistrano
lock '3.4.0'

set :application, 'lorry'

# Git repository
set :scm, :git
set :repo_url, 'git@github.com:beheh/clonklorry.git'

# Default branch is :master, can be overridden
set :branch, ENV['REVISION'] || ENV['BRANCH_NAME'] || 'master'

# Logging
set :format, :pretty
set :log_level, :info

# Symlinks shared between deployments
set :linked_files, fetch(:linked_files, []) + %w(config/lorry.yml config/tracking.html)
set :linked_dirs, fetch(:linked_dirs, []) + %w(upload logs)

# Example config files
set :config_example_suffix, '.example'

# Default value for keep_releases is 5
set :keep_releases, 5

# Add composer command
SSHKit.config.command_map[:composer] = "composer"

# Custom composer options
set :composer_install_flags, '--no-dev --prefer-dist --no-interaction --optimize-autoloader'

# Transfer config files to server
before 'deploy:check:linked_files', 'config:push'

# Setup consoles
set :lorry_console_path, "app/console"
set :doctrine_console_path, "vendor/bin/doctrine"
