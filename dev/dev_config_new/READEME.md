# Dev Config

This directory contains separate files that split up the main config.php file and makes it a bit easier
to make changes.

1) Copy `dev_config_new` to `dev_config`

2) Edit your real `config.php` file to include the dev one:

    require(__DIR__.'/dev/dev_config/dev_config.php');

3) Edit `dev_config.php` with db info

Or don't use this at all. It's up to you :-)
