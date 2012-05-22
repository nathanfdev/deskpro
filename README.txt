######################################################
# Quick Guide to Installing DeskPRO v4               #
######################################################

1. Extract the DeskPRO files to your web server
2. Make sure the /data folder is writable by the server
3. Create a new MySQL database.
4. Copy /config.new.php to /config.php
5. Edit /config.php with a text editor and insert your database details near the top.
6. Open DeskPRO in your web browser and the install wizard will start.

USING LINUX?
  | - 7. Schedule a cron task to run /cron.php every minute

USING WINDOWS?
  | - 7. Run /schedule.bat to create a scheduled task that runs cron.php every minute

Done!

######################################################
# Quick Guide to Keeping DeskPRO v4 up to Date       #
######################################################

The following process will download the latest files, backup your database and old files and
upgrade your database.

USING LINUX?
  | - 1. Either Use SSH to connect to your server or if you have a local install open a new terminal window.
  | - 2. Change to the DeskPRO v4 directory. For example: cd /var/www/DeskPRO
  | - 3. Execute the command: /usr/local/bin/php upgrade.php
        (The location of PHP will depend upon where you have PHP installed; update appropriately)

USING WINDOWS?
  | - 1. From the Start menu choose "Run" and enter "cmd.exe"
  | - 2. Change to the DeskPRO v4 directory. For example: cd C:\wamp\DeskPRO
  | - 3. Execute the command: "C:\wamp\bin\php\php5.3.0\php.exe" upgrade.php
       (The location of PHP will depend upon where you have PHP installed; update appropriately)

Done!

######################################################
# Quick Guide to Importing from DeskPRO v3/v2/v1     #
######################################################

1. Extract the DeskPRO files to your web server
2. Make sure the /data folder is writable by the server
3. Create a new MySQL database. (DeskPRO imports data leaving your existing database untouched *)
4. Copy /config.new.php to /config.php
5. Edit /config.php with a text editor and insert your *NEW* database details near the top.
6. Further down the file, find the section titled "DeskPRO Import", enter your *OLD* database details

USING LINUX?
  | - 7. Either Use SSH to connect to your server or if you have a local install open a new terminal window.
  | - 8. Change to the DeskPRO v4 directory. For example: cd /var/www/DeskPRO
  | - 9. Execute the command: /usr/local/bin/php import.php
        (The location of PHP will depend upon where you have PHP installed; update appropriately)

USING WINDOWS?
  | - 7. From the Start menu choose "Run" and enter "cmd.exe"
  | - 8. Change to the DeskPRO v4 directory. For example: cd "C:\wamp\DeskPRO"
  | - 9. Execute the command: "C:\wamp\bin\php\php5.3.0\php.exe" import.php
       (The location of PHP will depend upon where you have PHP installed; update appropriately)

10. Follow the on-screen instructions.
11. Log into DeskPRO v4 using your browser

USING LINUX?
  | - 12. Schedule a cron task to run /cron.php every minute

USING WINDOWS?
  | - 12. Run /schedule.bat to create a scheduled task that runs cron.php every minute

Done!

* For very old DeskPRO installations (released before 17th Setember 2008 - DeskPRO v3.2.2 and earlier) the
system will need to upgrade your existing installation. The upgrader will alert you to this and recommend
appropriate backup options. If you are running a trial upgrade to DeskPRO v4 and are leaving your current
DeskPRO v3 operational; you should clone your current DeskPRO v3 database before running an import.

######################################################
# Getting Help and Submitting Bugs                   #
######################################################

If you encounter a bug or an error, please email us with as much detail as you can to support@deskpro.com

To receive technical support, email support@deskpro.com or visit our helpdesk at http://helpdesk.deskpro.com/