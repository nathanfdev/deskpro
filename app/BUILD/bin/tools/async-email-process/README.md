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
* * * * * /usr/bin/flock -w 0 /tmp/dp-process.lock /path/to/bin/console email:process--load-config
```
