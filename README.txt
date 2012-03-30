######################################################
# Quick Guide to Installing DeskPRO v4               #
######################################################

1. Create a new MySQL database.
2. Copy /config.new.php to /config.php
3. Edit /config.php with a text editor and insert your database details near the top.
4. Open DeskPRO in your web browser and the install wizard will start.
5. Schedule a cron tasks (linux) or a scheduled task (windows) to run cron.php every minute

Done!

######################################################
# Quick Guide to Upgrading from DeskPRO v3/v2/v1     #
######################################################

1. Create a new MySQL database. (DeskPRO imports data leaving your existing database untouched *)
2. Copy /config.new.php to /config.php
3. Edit /config.php with a text editor and insert your *NEW* database details near the top.
4. Further down the file, find the section titled "DeskPRO Import", enter your *OLD database details

USING LINUX?
  | - 5. Use SSH to connect to your server, or if you are using a desktop computer, open a new terminal window.
  | - 6. Change to the DeskPRO v4 directory. For example: cd /var/www/DeskPRO
  | - 7. Execute the command: /usr/local/bin/php import.php
        (The location of PHP will depend upon where you have PHP installed; update appropriately)

USING WINDOWS?
  | - 5. From the Start menu choose "Run" and enter "cmd.exe"
  | - 6. Change to the DeskPRO v4 directory. For example: cd C:\wamp\DeskPRO
  | - 7. Execute the command: c:\wamp\bin\php\php5.3.0\php.exe import.php
       (The location of PHP will depend upon where you have PHP installed; update appropriately)

8. Follow the on-screen instructions.
9. Log into DeskPRO using your browser
10. Schedule a cron tasks (linux) or a scheduled task (windows) to run cron.php every minute

Done!

* For very old DeskPRO installations (released before 17th Setember 2008 - DeskPRO v3.2.2 and earlier) the
system will need to upgrade your existing installation. The upgrader will alert you to this and recommend
appropriate backup options.

######################################################
# Getting Help and Submitting Bugs                   #
######################################################

If you encounter a bug or an error, please email us with as much detail as you can to support@deskpro.com

To receive technical support, email support@deskpro.com or visit our helpdesk at http://support.deskpro.com/


