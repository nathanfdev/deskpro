ABOUT
-----

Async email processing improves the time it takes to process incoming email by creating two long-running tasks:

a) Email collection : Continuously reads incoming email from the defined accounts

b) Email processing : Processes new messages asynchronously as they are downloaded

REQS
----

1. A Redis server.

2. Install the config file to config/advanced/

HOWTO
-----

Create two cron jobs that run minutely. These jobs will spawn the tasks if they aren't already running.

```
* * * * * /usr/bin/flock -w 0 /tmp/dp-collect.lock /path/to/bin/console email:collection --load-config
* * * * * /usr/bin/flock -w 0 /tmp/dp-process.lock /path/to/bin/console email:process --load-config
```

Before enabling the cron job, it's adivsable to try the commands manually first -- so you can verify there are no
errors etc. Just run these from your terminal with the verbose flag. When you're satisfied, you can cancel
the process by hitting CTRL+C.

```
$ cd /path/to/deskpro
$ /usr/bin/flock -w 0 /tmp/dp-collect.lock bin/console email:collection --load-config -vvv
...CTRL+C
$ /usr/bin/flock -w 0 /tmp/dp-collect.lock bin/console email:process --load-config -vvv
...CTRL+C
```

LOGS
----

Logs are stored to var/logs/email.collection.log and var/logs/email.process.log. You can tail these files to get some
real-time visibility about what is going on:

```
$ tail -f var/logs/email.*
...
```
