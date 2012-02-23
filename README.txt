######################################################
# Installing DeskPRO v4                              #
######################################################

Preparation:

	1. Create a new MySQL database. You may need to ask your hosting provider or
	   systems administrator to do this for you.

Perform Installation:

	1. Copy /config.new.php to /config.php
	2. Edit /config.php with a text editor and insert your database details near the top.
	3. View DeskPRO in your web-browser. You should be greeted with the installation wizard.
	   Just proceed through the five install steps and DeskPRO will be installed and usable
	   in about two minutes.


######################################################
# Upgrading to DeskPROv4 from DeskPRO v1, v2 or v3   #
######################################################

The upgrade process between previous versions of DeskPRO has been streamlined. You just need
to run a single command and you can walk away until it finishes.

How It Works:

	The upgrader will first upgrade your OLD installation to the latest v3.5 version. If your
	helpdesk is already up to date, then this step is skipped.

	Then the upgrader will run through a series of steps that copies information like tickets,
	users, categories etc into a NEW database for DeskPRO v4.

	After the upgrade is complete, you can start using your DeskPROv4 installation right
	away using the same tech/admin login as you did before.

Preparation:

	1. Create a new MySQL database for v4. You may need to ask your hosting provider or
	   systems administrator to do this for you.
	2. Obtain database details for your OLD installation of DeskPRO. You can get this
	   information by looking at /includes/config.php in the directory of the old DeskPRO.

Configuration:

	1. Copy /config.new.php to /config.php
	2. Edit /config.php with a text editor and insert the database details for your NEW database near the top.
	3. Further down the file, find the section titled "DeskPRO Import"
	   Edit this section with the details of your OLD DeskPRO installation.

	   If you would like to move attachments to the filesystem at the same time as you're upgrading,
	   enable the `store_attachment_files` option.

	   If you are already storing attachments in the filesystem in your old DeskPRO installation, then you
	   must specify the storage path in the `existing_attachment_files` option.

Perform Upgrade on Windows:

	1. From the Start menu choose "Run" and enter "cmd.exe"
	2. Change to the DeskPRO v4 directory. For example: cd C:\wamp\DeskPROv4
	3. Execute the command: php.exe upgrade.php

	   Note that you may need to specify a full path for the location of php.exe.
	   For example: c:\wamp\bin\php\php5.3.0\php.exe upgrade.php

	   The location of php.exe will depend on how you installed your web server and PHP itself.
	   You may need to as your hosting provider or your systems administrator.

	4. Follow the on-screen instructions. Once the upgrader starts processing your data, no interaction
	   is required on your part and you can just walk away while it processes.

Perform Upgrade on Linux:

	1. Use SSH to connect to your server, or if you are using a desktop computer, open a new terminal window.
	2. Change to the DeskPRO v4 directory. For example: cd /var/www/DeskPROv4
	3. Execute the command: php upgrade.php

	   Note that you may need to specify a full path for the location of php.exe.
	   For example: /usr/local/bin/php upgrade.php

	   The location of php.exe will depend on how you installed your web server and PHP itself.
	   You may need to as your hosting provider or your systems administrator.

	4. Follow the on-screen instructions. Once the upgrader starts processing your data, no interaction
	   is required on your part and you can just walk away while it processes.


######################################################
# Getting Help and Submitting Bugs                   #
######################################################

If you encounter a bug or an error, please email us with as much detail as you can to support@deskpro.com

To receive technical support, email support@deskpro.com or visit our helpdesk at http://helpdesk.deskpro.com/
