# Using PHP's built-in server

PHP's built-in server is convenient for local development, but very inefficient and unsafe for production environments.

To run the server, cd to the project directory and then run the server like this:

    $ cd /path/to/deskpro
    $ php -S 0.0.0.0:8000 dev/php-server/routing.php
