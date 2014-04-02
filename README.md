Lorry
=====
A website to host and showcase released Clonk addons and their required packages.


Requirements
------------
The application requires a web server with at least PHP>=5.5 and the extensions json, pecl_http, openssl, gettext and curl. Running the application with missing extensions might be possible to some extent, but is not supported or encouraged.

A database and the corresponding PHP PDO-extension should also be available. Lorry was developed and tested with an up-to-date MySQL installation.

All requirements can be checked by executing the standalone file `install.php` in the /app directory.


Deploying
----------
To deploy the application, clone the source and point your webserver to the web/-directory. The other directories must not be publicly accessible.

Lorry uses composer for dependency management, so simply execute a `composer install` in the root application directory to install all required libraries.

The sql schema in /app/sql/lorry.sql needs to executed in your database. At some point later in development this might be done automatically when installing.

In /web/.htaccess you might have to add a RewriteBase with the base of the web directory, depending on your server configuration.

You also need to set up the main configuration file, which the application expects at `app/config/lorry.php`. You can copy the existing `lorry.example.php` in the same directory and modify the values.


Roadmap
-------
- Design and implement approachable and presentable download pages for addons
- Design and implement a workflow for uploads
- Add a comment system
- Add a moderation log
- Finish implementing user moderation features
- Add a search function


Copying
-------
Copyright (C) 2014  Benedict Etzel

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.