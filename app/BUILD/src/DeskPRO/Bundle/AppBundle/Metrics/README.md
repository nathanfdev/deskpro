# Interesting Events

## agent.created

Triggered when a new agent is added.

* `activeAgents`: The total number of agents

## apiKey.created

Triggered when a new API key is created.

## usersource.created

Triggered when a new auth/SSO user source is added.

* `area`: "agent" or "user"
* `type`: The usersource type e.g. "deskpro_us_active_directory"

## emailAccount.customEmailAddress

Triggered when the user sets a custom email address on an email account.

* `email`: The custom email address set

## brand.created

Triggered when a new brand is added.

* `brandName`: The name of the brand added

## importer.started

Triggered when an importer is started from the admin interface.

* `type`: The system being import from. e.g. "zendesk" or "kayako"
* `account`: The account name being imported from. e.g. foobar.zendesk.com

---

# Reacting to interesting events

In `config/advanced/config.env.php` add a handler:

```php
$ENV_CONFIG['interesting_events_fn'] = function(\DeskPRO\Bundle\AppBundle\Metrics\InterestingEvent $event) {
    $id   = $event->getId();     // event id. e.g. importer.started
    $info = $event->getInfo();   // event data. e.g. $info['account']
};
```