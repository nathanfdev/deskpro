IMPORT BUNDLE
==================

## Overview

* [Commands](#commands)
    - [Command #1. Export check](#command-1-export-check)
    - [Command #2. Export run](#command-2-export-run)
    - [Command #3. Import run](#command-3-import-run)
    - [Command #4. Import batch run](#command-4-import-batch-run)
* [Exporters](#exporters)
    - [Exporter #1. CSV](#exporter-1-csv)
        * [CSV Files map](#csv-files-map)
        * [CSV Entity #1. Article](#csv-entity-1-article)
        * [CSV Entity #2. Download](#csv-entity-2-download)
        * [CSV Entity #3. Feedback](#csv-entity-3-feedback)
        * [CSV Entity #4. News](#csv-entity-4-news)
        * [CSV Entity #5. Person](#csv-entity-5-person)
        * [CSV Entity #6. Ticket](#csv-entity-6-ticket)
        * [CSV Entity #7. Organization](#csv-entity-7-organization)
    - [Exporter #2. OsTicket](#exporter-2-osticket)
    - [Exporter #3. ZenDesk](#exporter-3-zendesk)

Commands
------------

#### Command #1. Export check

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


#### Command #2. Export run

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


#### Command #3. Import run

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


#### Command #4. Import batch run

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

#### Exporter #1. CSV

Exports data from CSV files.

###### CSV Files map

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
    - organization_contact_data.csv
    - organization_custom_fields.csv

Example: `app/src/Application/ImportBundle/Resources/example/csv`

###### CSV Entity #1. Article

**articles.csv**

| Column name                | Description                                                                   |
| -------------------------- |-------------------------------------------------------------------------------|
| id                         | is optional, use as reference for `article_custom_fields.csv`                 |
| person                     | means a person email                                                          |
| title                      |                                                                               |
| content                    |                                                                               |
| slug                       | could be empty (generated from title)                                         |
| language                   |                                                                               |
| status                     |                                                                               |
| category                   | could be empty                                                                |
| label                      | could be empty (only one label is supported)                                  |
| date_created               | created could be empty (current time)                                         |
| custom "Custom field name" | could be multiple, see `example/csv_inline_custom_data/articles.csv`          |

**article_custom_fields.csv**

| Column name                | Description                                                                   |
| -------------------------- |-------------------------------------------------------------------------------|
| article_id                 | reference to `articles.csv`                                                   |
| field_name                 |                                                                               |
| value                      |                                                                               |

###### CSV Entity #2. Download

**downloads.csv**

| Column name                | Description                                                                   |
| -------------------------- |-------------------------------------------------------------------------------|
| person                     | means a person email                                                          |
| title                      |                                                                               |
| content                    |                                                                               |
| slug                       | could be empty (generated from title)                                         |
| language                   |                                                                               |
| category                   | could be empty                                                                |
| status                     |                                                                               |
| date_created               | created could be empty (current time)                                         |
| label                      | could be empty (only one label is supported)                                  |
| category                   | could be empty                                                                |
| blob_url                   |                                                                               |
| blob_path                  |                                                                               |
| file_name                  |                                                                               |
| content_type               |                                                                               |
| is_inline                  | boolean                                                                       |

###### CSV Entity #3. Feedback

**feedback.csv**

| Column name                | Description                                                                   |
| -------------------------- |-------------------------------------------------------------------------------|
| id                         | use as reference for `feedback_attachments.csv`, `feedback_custom_fields.csv` |
| person                     | means a person email                                                          |
| title                      |                                                                               |
| content                    |                                                                               |
| slug                       | could be empty (generated from title)                                         |
| language                   |                                                                               |
| category                   | could be empty                                                                |
| popularity                 |                                                                               |
| status                     |                                                                               |
| date_created               | created could be empty (current time)                                         |
| date_published             | created could be empty (current time)                                         |
| label                      | could be empty (only one label is supported)                                  |
| custom "Custom field name" | could be multiple, see `example/csv_inline_custom_data/feedback.csv`          |

**feedback_attachments.csv**

| Column name                | Description                                                                   |
| -------------------------- |-------------------------------------------------------------------------------|
| feedback_id                | reference to `feedback.csv`                                                   |
| person                     | means a person email                                                          |
| blob_url                   |                                                                               |
| blob_path                  |                                                                               |
| file_name                  |                                                                               |
| content_type               |                                                                               |
| is_inline                  | boolean                                                                       |

**feedback_custom_fields.csv**

| Column name                | Description                                                                   |
| -------------------------- |-------------------------------------------------------------------------------|
| feedback_id                | reference to `feedback.csv`                                                   |
| field_name                 |                                                                               |
| value                      |                                                                               |

###### CSV Entity #4. News

**news.csv**

| Column name                | Description                                                                   |
| -------------------------- |-------------------------------------------------------------------------------|
| person                     | means a person email                                                          |
| title                      |                                                                               |
| content                    |                                                                               |
| slug                       | could be empty (generated from title)                                         |
| language                   |                                                                               |
| status                     |                                                                               |
| date_created               | created could be empty (current time)                                         |
| date_published             | created could be empty (current time)                                         |
| category                   | could be empty                                                                |
| label                      | could be empty (only one label is supported)                                  |

###### CSV Entity #5. Person

**people.csv**

| Column name                | Description                                                                   |
| -------------------------- |-------------------------------------------------------------------------------|
| id                         | is optional, use as reference for `people_custom_fields.csv`                  |
| name                       |                                                                               |
| email                      |                                                                               |
| is_agent                   | boolean                                                                       |
| custom "Custom field name" | could be multiple, see `example/csv_inline_custom_data/people.csv`            |

**people_custom_fields.csv**

| Column name                | Description                                                                   |
| -------------------------- |-------------------------------------------------------------------------------|
| person_id                  | reference to `people.csv` `id` or `email` column                              |
| field_name                 |                                                                               |
| value                      |                                                                               |

###### CSV Entity #6. Ticket

**tickets.csv**

| Column name                | Description                                                                   |
| -------------------------- |-------------------------------------------------------------------------------|
| id                         | use as reference for `ticket_messages.csv`, `ticket_custom_fields.csv`        |
| subject                    |                                                                               |
| user                       | means a person email                                                          |
| agent                      | means a person email                                                          |
| status                     |                                                                               |
| date_created               | created could be empty (current time)                                         |
| custom "Custom field name" | could be multiple, see `example/csv_inline_custom_data/tickets.csv`           |

**ticket_messages.csv**

| Column name                | Description                                                                   |
| -------------------------- |-------------------------------------------------------------------------------|
| ticket_id                  | reference to `tickets.csv`                                                    |
| message_id                 |                                                                               |
| message_text               |                                                                               |
| user                       | means a person email                                                          |
| date_created               | created could be empty (current time)                                         |

**ticket_attachments.csv**

| Column name                | Description                                                                   |
| -------------------------- |-------------------------------------------------------------------------------|
| message_id                 | reference to `ticket_messages.csv`                                            |
| person                     | means a person email                                                          |
| blob_url                   |                                                                               |
| blob_path                  |                                                                               |
| file_name                  |                                                                               |
| content_type               |                                                                               |
| is_inline                  | boolean                                                                       |

**ticket_custom_fields.csv**

| Column name                | Description                                                                   |
| -------------------------- |-------------------------------------------------------------------------------|
| ticket_id                  | reference to `tickets.csv`                                                    |
| field_name                 |                                                                               |
| value                      |                                                                               |

###### CSV Entity #7. Organization

**organizations.csv**

| Column name                | Description                                                                   |
| -------------------------- |-------------------------------------------------------------------------------|
| id                         | is optional                                                                   |
| name                       |                                                                               |
| importance                 |                                                                               |
| date_created               | created could be empty (current time)                                         |
| blob_url                   |                                                                               |
| blob_path                  |                                                                               |
| file_name                  |                                                                               |
| content_type               |                                                                               |
| custom "Custom field name" | could be multiple, see `example/csv_inline_custom_data/organizations.csv`     |

 Example:
 
| name              | importance | blob_url                | file_name   | content_type |
|-------------------|------------|-------------------------|-------------|--------------|
| Some Organization | 1          | http://site.com/img.png | preview.png | image/png    | 

**organization_contact_data.csv**

| Column name                | Description                                                                   |
| -------------------------- |-------------------------------------------------------------------------------|
| organization_id            | reference to `organizations.csv` `id` or `name` column                        |
| contact_id                 |                                                                               |
| field_name                 | `contact_type`, `comment` and contact type specific parameters                |
| value                      |                                                                               |

Supported contact types:

 - address
 - facebook
 - fax
 - instant_message
 - linked_in
 - mobile
 - phone
 - skype
 - twitter
 - website

1) "address" contact type:
 
| organization_id   | contact_id | field_name           | value                        |
|-------------------|------------|----------------------|------------------------------|
| Some Organization | contact_1  | contact_type         | address                      |
| Some Organization | contact_1  | comment              | some comment                 |
| Some Organization | contact_1  | address              | address                      |
| Some Organization | contact_1  | city                 | city                         |
| Some Organization | contact_1  | state                | state                        |
| Some Organization | contact_1  | zip                  | zip                          |
| Some Organization | contact_1  | country              | country                      |

2) "facebook" contact type:

| organization_id   | contact_id | field_name           | value                        |
|-------------------|------------|----------------------|------------------------------|
| Some Organization | contact_2  | contact_type         | facebook                     |
| Some Organization | contact_2  | comment              | some comment                 |
| Some Organization | contact_2  | profile_url          | http:://facebook.com/profile |

3) "fax" contact type:

| organization_id   | contact_id | field_name           | value                        |
|-------------------|------------|----------------------|------------------------------|
| Some Organization | contact_3  | contact_type         | fax                          |
| Some Organization | contact_3  | comment              | some comment                 |
| Some Organization | contact_3  | country_calling_code | +7                           |
| Some Organization | contact_3  | number               | 1234567                      |
| Some Organization | contact_3  | type                 | phone                        |

4) "instant_message" contact type:

| organization_id   | contact_id | field_name           | value                        |
|-------------------|------------|----------------------|------------------------------|
| Some Organization | contact_4  | contact_type         | instant_message              |
| Some Organization | contact_4  | username             | some_user                    |
| Some Organization | contact_4  | service              | service                      |

5) "mobile" contact type:

| organization_id   | contact_id | field_name           | value                        |
|-------------------|------------|----------------------|------------------------------|
| Some Organization | contact_5  | contact_type         | mobile                       |
| Some Organization | contact_5  | comment              | some comment                 |
| Some Organization | contact_5  | country_calling_code | +7                           |
| Some Organization | contact_5  | number               | 1234567                      |
| Some Organization | contact_5  | type                 | phone                        |

6) "phone" contact type:

| organization_id   | contact_id | field_name           | value                        |
|-------------------|------------|----------------------|------------------------------|
| Some Organization | contact_6  | contact_type         | phone                        |
| Some Organization | contact_6  | comment              | some comment                 |
| Some Organization | contact_6  | country_calling_code | +7                           |
| Some Organization | contact_6  | number               | 1234567                      |
| Some Organization | contact_6  | type                 | phone                        |

**organization_custom_fields.csv**

| Column name                | Description                                                                   |
| -------------------------- |-------------------------------------------------------------------------------|
| organization_id            | reference to `organizations.csv` `id` or `name` column                        |
| field_name                 |                                                                               |
| value                      |                                                                               |

#### Exporter #2. OsTicket

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

#### Exporter #3. ZenDesk

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