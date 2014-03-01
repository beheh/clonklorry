Lorry
=====
A website to host and showcase released Clonk addons and their required packages.


Requirements
------------
The application requires a web server with at least PHP>=5.5 and the extensions json, pecl_http, openssl, gettext and curl. Running the application with missing extensions might be possible to some extent, but is not supported or encouraged.

A database and the corresponding PHP PDO-extension should also be available. Lorry was developed and tested with an up-to-date MySQL installation.

All requirements can be checked by executing the standalone file `install.php` in the /app directory.


Deployment
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
