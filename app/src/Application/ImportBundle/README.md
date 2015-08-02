IMPORT BUNDLE
==================

Commands
------------

**1) Export check**

Reads and validates data from an external source.

```bash
php cmd.php dp:export:check exporter_type [--input-path=] [--verbose]
```

**exporter_type**

Supported types: "csv", "json", "osticket", "zendesk".

**--input-path=**

Location of source data for "csv" and "json" exporters.

**--batch-config=**

Path to custom batch config file.

**--verbose**

Shows log output in the console.

**--silent**

Disables all console output.

Examples:
```bash
php cmd.php dp:export:check csv --input-path="app/src/Application/ImportBundle/Resources/example/csv" --verbose
```
```bash
php cmd.php dp:export:check osticket --verbose
```
```bash
php cmd.php dp:export:check osticket --batch-config="app/src/Application/ImportBundle/Resources/example/osticket.batch.json" --verbose
```
```bash
php cmd.php dp:export:check zendesk --verbose
```

**2) Export run**

Reads data from an external source, validate entities and write them to json files.
Export could be run in json files only.

```bash
php cmd.php dp:export:run exporter_type [--input-path=] [--output-path=""] [--verbose] [--dry-run]
```

**exporter_type**

Supported types: "csv", "json", "osticket", "zendesk".

**--input-path=**

Csv and json exporter types need input path to be specified.

**--output-path=**

Destination path of generating json files.

**--batch-config=**

Path to custom batch config file. If it is not specified, exporter looks for a batch config in output path dir

**--verbose**

Shows log output in the console.

**--silent**

Disables all console output.

**--dry-run**

Test run, json files are not generated. If dry run mode is enabled "output-path" is not required.

Examples:

```bash
php cmd.php dp:export:run csv --input-path="app/src/Application/ImportBundle/Resources/example/csv" \
    --output-path="app/src/Application/ImportBundle/Resources/example/csv_to_json" --verbose
```

```bash
php cmd.php dp:export:run osticket --output-path="app/src/Application/ImportBundle/Resources/example/osticket_to_json" --verbose
```
```bash
php cmd.php dp:export:run osticket --output-path="app/src/Application/ImportBundle/Resources/example/osticket_to_json" \
    --batch-config="app/src/Application/ImportBundle/Resources/example/osticket.batch.json" --verbose
```
```bash
php cmd.php dp:export:run osticket --output-path="app/src/Application/ImportBundle/Resources/example/osticket_to_json_fixtures"
```

```bash
php cmd.php dp:export:run zendesk --output-path="app/src/Application/ImportBundle/Resources/example/zendesk_to_json" --verbose
```


**3) Import run**

Reads data from an external source, validate entities and import them to deskpro database.

```
php cmd.php dp:import:run exporter_type [--input-path=] [--verbose] [--dry-run]
```

**exporter_type**

Supported types: "csv", "json", "osticket", "zendesk".

**--input-path=**

Csv and json exporter types need input path to be specified.

**--verbose**

Shows log output in the console.

**--silent**

Disables all console output.

**--dry-run**

Test run, entities are not imported to deskpro database.

Examples:
```
php cmd.php dp:import:run csv --input-path="app/src/Application/ImportBundle/Resources/example/csv" --verbose
```
```bash
php cmd.php dp:import:run json --input-path="app/src/Application/ImportBundle/Resources/example/osticket_to_json" --verbose
```
```bash
php cmd.php dp:import:run osticket --output-path="app/src/Application/ImportBundle/Resources/example/osticket" --verbose
```
```bash
php cmd.php dp:import:run zendesk --output-path="app/src/Application/ImportBundle/Resources/example/zendesk" --verbose
```


**4) Import batch run**

Reads data from an external source and writes to json files. Then reads json files and imports to database.

```
php cmd.php dp:import:batch exporter_type [--input-path=] [--output-path=""] [--verbose] [--dry-run]
```

**exporter_type**

Supported types: "csv", "json", "osticket", "zendesk".

**--input-path=**

Csv exporter need input path to be specified.

**--output-path=**

Destination path of generating json files.

**--batch-config=**

Path to custom batch config file. If it is not specified, exporter looks for a batch config in output path dir

**--verbose**

Shows log output in the console.

**--silent**

Disables all console output.

**--dry-run**

Test run, json files are not generated. If dry run mode is enabled "output-path" is not required.

Examples:
```bash
php cmd.php dp:import:batch osticket --output-path="app/src/Application/ImportBundle/Resources/example/osticket_to_json2" --verbose
```
```bash
php cmd.php dp:import:batch zendesk --output-path="app/src/Application/ImportBundle/Resources/example/zendesk_to_json2" --verbose
```


Exporters
------------

**1) CSV**

Exports data from CSV files.

**Files map**
 - articles.csv
 - article_custom_fields.csv
 - downloads.csv
 - feedback.csv
 - feedback_attachments.csv
 - feedback_custom_fields.csv
 - news.csv
 - people.csv
 - people_custom_fields.csv
 - tickets.csv
 - ticket_messages.csv
 - ticket_attachments.csv
 - ticket_custom_fields.csv
 - organizations.csv
 - organization_custom_fields.csv

Example:
app/src/Application/ImportBundle/Resources/example/csv

**Articles**
- Articles required columns

    'id' is optional,
    'person',
    'title',
    'content',
    'slug',
    'language',
    'status',
    'category',
    'label',
    'date_created',
    'custom "Custom field name"' could be multiple (see example/csv_inline_custom_data/articles.csv)
    
    Person means a person email
    Slug could be empty (generated from title)
    Date created could be empty (current time)
    Label could be empty (only one label is supported)
    Category could be empty


 - Article custom fields required columns

    'article_id',
    'field_name',
    'value'

**Downloads**
 - Downloads required columns

    'person',
    'title',
    'content',
    'slug',
    'language',
    'category',
    'status',
    'date_created',
    'label'
    'blob_url',
    'blob_path',
    'file_name',
    'content_type',
    'is_inline'

    Person means a person email
    Slug could be empty (generated from title)
    Date created could be empty (current time)
    Label could be empty (only one label is supported)
    Category could be empty

**Feedback**
- Feedback required columns

    'id',
    'person',
    'title',
    'content',
    'slug',
    'language',
    'popularity',
    'status',
    'category',
    'label',
    'date_created',
    'date_published',
    'custom "Custom field name"' could be multiple (see example/csv_inline_custom_data/feedback.csv)

    Person means a person email
    Slug could be empty (generated from title)
    Date created could be empty (current time)
    Date published could be empty
    Label could be empty (only one label is supported)
    Category could be empty


- Feedback attachments required columns

    'feedback_id',
    'person',
    'blob_url',
    'blob_path',
    'file_name',
    'content_type',
    'is_inline'

    Person means a person email


- Feedback custom fields required columns

    'feedback_id'
    'field_name'
    'value'

**News**
- News required columns

    'person',
    'title',
    'content',
    'slug',
    'language',
    'status',
    'date_created',
    'date_published',
    'category',
    'label'

    Person means a person email
    Slug could be empty (generated from title)
    Date created could be empty (current time)
    Date published could be empty
    Label could be empty (only one label is supported)
    Category could be empty

**People**
- People required columns

    'id' is optional,
    'name',
    'email',
    'is_agent' is optional,
    'custom "Custom field name"' could be multiple (see example/csv_inline_custom_data/people.csv)


- People custom fields required columns

    'person_id' people.csv `id` or `email` column,
    'field_name',
    'value'

**Tickets**
- Tickets required columns

    'id',
    'subject',
    'user',
    'agent',
    'status',
    'date_created' is optional,
    'custom "Custom field name"' could be multiple (see example/csv_inline_custom_data/tickets.csv)

    User means a user email
    Agent means a agent email
    Date created could be empty (current time)


- Ticket messages required columns

    'ticket_id',
    'message_id',
    'message_text',
    'user',
    'date_created' is optional

    User means a user email
    Date created could be empty (current time)


- Ticket attachments required columns

    'message_id',
    'person',
    'blob_url',
    'blob_path',
    'file_name',
    'content_type',
    'is_inline'

    Person means a person email


- Ticket custom fields required columns

    'ticket_id',
    'field_name',
    'value'

**2) OsTicket**

Exports data from OsTicket database.

Configuration
Add `osticket_import` configuration to your www/config.php

```php
$DP_CONFIG['osticket_import'] = array(
    'db_host'       => 'localhost',
    'db_name'       => 'os_ticket',
    'db_username'   => 'root',
    'db_password'   => 'deskpro'
);
```

Fixtures:

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` PROCEDURE `user_fixtures`()
BEGIN
   DECLARE x INT;
   DECLARE str VARCHAR(255);
   SET x = 1000;
   WHILE x <= 10000 DO
       INSERT INTO ost_user
       (org_id, default_email_id, status, name, created, updated)
       VALUES
       (1, x, 0, CONCAT('name', x), NOW(), NOW());

       INSERT INTO ost_user_email (user_id, address)
       VALUES
       (LAST_INSERT_ID(), CONCAT('e', x, '@osticket.com'));

       SET x = x + 1;
   END WHILE;
END$$
DELIMITER ;
```

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` PROCEDURE `staff_fixtures`()
BEGIN
   DECLARE x INT;
   DECLARE str VARCHAR(255);
   SET x = 1000;
   WHILE x <= 10000 DO
       INSERT INTO ost_staff
       (email, username, firstname, lastname, timezone_id, created, isadmin, group_id)
       VALUES
       (
            CONCAT('se', x, '@osticket.com'),
            CONCAT('username', x),
            CONCAT('firstname', x),
            CONCAT('lastname', x),
            FLOOR(1 + (RAND() * 29)),
            NOW(),
            ROUND(RAND()),
            ROUND(1 + RAND() * 2)
        );

       SET x = x + 1;
   END WHILE;
END$$
DELIMITER ;
```

**3) ZenDesk**

Exports data from ZenDesk account.

Configuration
Add `zendesk_import` configuration to your www/config.php

```php
$DP_CONFIG['zendesk_import'] = array(
    'subdomain'          => 'your account subdomain',
    'username'           => 'email@deskpro.com',
    'password'           => '',
    'api_token'          => '',
    'initial_time'       => '2013-01-01 00:00:00',
    'connection_timeout' => 60,
);
```

Supported tickets and people data export.


Support for batching
------------

Common properties:

 - id
 - type
 - date_created
 - date_modified

**1) output.batch.json**

Generates by exporters whitch has support for batching (osticket and zendesk). Specific for each exporter.

**2) input.batch.json**

Generates by json exporter.