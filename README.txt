DeskPRO
=======

Installation
------------

For detailed instructions on how to install DeskPRO, refer to our installation guide:

    http://wwww.deskpro.com/install-guide

Quick overview:

    1. Copy /config.new.php to /config.php
    2. Open /config.php with a text editor and edit the database options
    3. In your web-browser, view /index.php/install/ to start the web-based installer.
       For example: http://www.mysite.com/deskpro/index.php
    4. Follow the on-screen instructions.

Email Gateway Tasks
-------------------

Set up a scheduled task to process email every 1-5 minutes:

	/usr/bin/php /path/to/deskpro/appfiles/bin/console.php dp:process-email-gateways

Where `/usr/bin/php` is the path to the PHP CLI binary.

Support
-------

To receive support, email support@deskpro.com or visit our helpdesk at http://helpdesk.deskpro.com/
