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
        * [CSV Files structure (input-path)](#csv-files-structure-input-path)
        * [CSV Entity #1. Article](#csv-entity-1-article)
        * [CSV Entity #2. Article Category](#csv-entity-2-article-category)
        * [CSV Entity #3. Download](#csv-entity-3-download)
        * [CSV Entity #4. Feedback](#csv-entity-4-feedback)
        * [CSV Entity #5. News](#csv-entity-5-news)
        * [CSV Entity #6. Person](#csv-entity-6-person)
        * [CSV Entity #7. Ticket](#csv-entity-7-ticket)
        * [CSV Entity #8. Organization](#csv-entity-8-organization)
    - [Exporter #2. JSON](#exporter-2-json)
        * [JSON Files structure (input-path)](#json-files-structure-input-path)
        * [JSON Entity #1. Article](#json-entity-1-article)
        * [JSON Entity #2. Article Category](#json-entity-2-article-category)
        * [JSON Entity #3. Download](#json-entity-3-download)
        * [JSON Entity #4. Feedback](#json-entity-4-feedback)
        * [JSON Entity #5. News](#json-entity-5-news)
        * [JSON Entity #6. Person](#json-entity-6-person)
        * [JSON Entity #7. Ticket](#json-entity-7-ticket)
        * [JSON Entity #8. Organization](#json-entity-8-organization)
        * [JSON Batch.json](#json-batchjson)
    - [Exporter #3. OsTicket](#exporter-3-osticket)
        * [OsTicket Configuration](#osticket-configuration)
        * [OsTicket Fixtures](#osticket-fixtures)
    - [Exporter #4. ZenDesk](#exporter-4-zendesk)
        * [ZenDesk Configuration](#zendesk-configuration)
        * [ZenDesk Fixtures](#zendesk-fixtures)
            - [ZenDesk Fixtures Core API](#zendesk-fixtures-core-api)
            - [ZenDesk Fixtures Help Center](#zendesk-fixtures-help-center)
        * [ZenDesk Batch.json](#zendesk-batchjson)
    - [Exporter #5. DeskPRO](#exporter-5-deskpro)

## Commands

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

Parses data from an external source to json files.

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

Path to custom batch config file. If it is not specified, exporter looks for a batch config in output path dir.

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

Parses data from an external source to the DeskPRO database.

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


### Command #4. Import batch run

Parses data from an external source to json files and the DeskPRO database.

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

Path to custom batch config file. If it is not specified, exporter looks for a batch config in output path dir.

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


## Exporters

#### Exporter #1. CSV

Exports data from CSV files. Batching not supported.

##### CSV Files structure (input-path)

 - **input_path/**
     - [articles.csv](#articlescsv)
        - [article_custom_fields.csv](#article_custom_fieldscsv)
     - [downloads.csv](#downloadscsv)
     - [feedback.csv](#feedbackcsv)
        - [feedback_attachments.csv](#feedback_attachmentscsv)
        - [feedback_custom_fields.csv](#feedback_custom_fieldscsv)
     - [news.csv](#newscsv)
     - [people.csv](#peoplecsv)
        - [people_contact_data.csv](#people_contact_datacsv)
        - [people_custom_fields.csv](#people_custom_fieldscsv)
     - [tickets.csv](#ticketscsv)
        - [ticket_messages.csv](#ticket_messagescsv)
        - [ticket_attachments.csv](#ticket_attachmentscsv)
        - [ticket_custom_fields.csv](#ticket_custom_fieldscsv)
     - [organizations.csv](#organizationscsv)
        - [organization_contact_data.csv](#organization_contact_datacsv)
        - [organization_custom_fields.csv](#organization_custom_fieldscsv)

Example: `app/src/Application/ImportBundle/Resources/example/csv`

##### CSV Entity #1. Article

###### articles.csv

| Column name                | Description                                                                             |
| -------------------------- |-----------------------------------------------------------------------------------------|
| id                         | is optional, use as reference for `article_custom_fields.csv`                           |
| person                     | means a person email                                                                    |
| title                      |                                                                                         |
| content                    |                                                                                         |
| language                   |                                                                                         |
| status                     |                                                                                         |
| category                   | could be empty                                                                          |
| label                      | could be empty (only one label is supported)                                            |
| date_created               | created could be empty (current time)                                                   |
| custom "Custom field name" | could be multiple, see `example/csv_inline/articles.csv`                                |

###### article_custom_fields.csv

| Column name                | Description                                                                             |
| -------------------------- |-----------------------------------------------------------------------------------------|
| article_id                 | reference to `articles.csv`                                                             |
| field_name                 |                                                                                         |
| value                      |                                                                                         |

##### CSV Entity #2. Article Category

| Column name                | Description                                                                             |
| -------------------------- |-----------------------------------------------------------------------------------------|
| title                      | Title tree path, e.g. "Category 1 > Subcategory 1"                                      |
| is_agent                   | could be empty (by default "false")                                                     |
| is_book                    | could be empty (by default "false")                                                     |

##### CSV Entity #3. Download

###### downloads.csv

| Column name                | Description                                                                             |
| -------------------------- |-----------------------------------------------------------------------------------------|
| person                     | means a person email                                                                    |
| title                      |                                                                                         |
| content                    |                                                                                         |
| language                   |                                                                                         |
| category                   | could be empty                                                                          |
| status                     |                                                                                         |
| date_created               | created could be empty (current time)                                                   |
| label                      | could be empty (only one label is supported)                                            |
| category                   | could be empty                                                                          |
| blob_url                   |                                                                                         |
| blob_path                  |                                                                                         |
| file_name                  |                                                                                         |
| content_type               |                                                                                         |
| is_inline                  | boolean                                                                                 |

##### CSV Entity #4. Feedback

###### feedback.csv

| Column name                | Description                                                                             |
| -------------------------- |-----------------------------------------------------------------------------------------|
| id                         | use as reference for `feedback_attachments.csv`, `feedback_custom_fields.csv`           |
| person                     | means a person email                                                                    |
| title                      |                                                                                         |
| content                    |                                                                                         |
| language                   |                                                                                         |
| category                   | could be empty                                                                          |
| popularity                 |                                                                                         |
| status                     |                                                                                         |
| date_created               | created could be empty (current time)                                                   |
| date_published             | created could be empty (current time)                                                   |
| label                      | could be empty (only one label is supported)                                            |
| custom "Custom field name" | could be multiple, see `example/csv_inline/feedback.csv`                                |

###### feedback_attachments.csv

| Column name                | Description                                                                             |
| -------------------------- |-----------------------------------------------------------------------------------------|
| feedback_id                | reference to `feedback.csv`                                                             |
| person                     | means a person email                                                                    |
| blob_url                   |                                                                                         |
| blob_path                  |                                                                                         |
| file_name                  |                                                                                         |
| content_type               |                                                                                         |
| is_inline                  | boolean                                                                                 |

###### feedback_custom_fields.csv

| Column name                | Description                                                                             |
| -------------------------- |-----------------------------------------------------------------------------------------|
| feedback_id                | reference to `feedback.csv`                                                             |
| field_name                 |                                                                                         |
| value                      |                                                                                         |

##### CSV Entity #5. News

###### news.csv

| Column name                | Description                                                                             |
| -------------------------- |-----------------------------------------------------------------------------------------|
| person                     | means a person email                                                                    |
| title                      |                                                                                         |
| content                    |                                                                                         |
| language                   |                                                                                         |
| status                     |                                                                                         |
| date_created               | created could be empty (current time)                                                   |
| date_published             | created could be empty (current time)                                                   |
| category                   | could be empty                                                                          |
| label                      | could be empty (only one label is supported)                                            |

##### CSV Entity #6. Person

###### people.csv

Base info:

| Column name                | Description                                                                             |
| -------------------------- |-----------------------------------------------------------------------------------------|
| id                         | is optional, use as reference for `people_custom_fields.csv`                            |
| name                       |                                                                                         |
| email                      |                                                                                         |
| is_agent                   | boolean                                                                                 |
| custom "Custom field name" | could be multiple, see `example/csv_inline/people.csv`                                  |


Contact info:

| Column name                | Description                                                                             |
| -------------------------- |-----------------------------------------------------------------------------------------|
| address                    |                                                                                         |
| city                       |                                                                                         |
| state                      |                                                                                         |
| post_code                  |                                                                                         |
| country                    |                                                                                         |
| facebook                   | Facebook profile URL                                                                    |
| im                         | InstantMessage username                                                                 |
| linkedin                   | LinkedIn profile URL                                                                    |
| mobile                     | Phone Number (unspaced and prefixed with country code e.g. +447700900315, +12025550156) |
| fax                        | Phone Number                                                                            |
| phone                      | Phone Number                                                                            |
| skype                      | Skype username                                                                          |
| twitter                    | Twitter username                                                                        |
| website                    | Website URL                                                                             |

###### people_custom_fields.csv

| Column name                | Description                                                                             |
| -------------------------- |-----------------------------------------------------------------------------------------|
| person_id                  | reference to `people.csv` `id` or `email` column                                        |
| field_name                 |                                                                                         |
| value                      |                                                                                         |

*Note:* You should use reference to `id` or `email` column, not both together.

###### people_contact_data.csv

| Column name                | Description                                                                             |
| -------------------------- |-------------------------------------------------------------------------------|
| person_id                  | reference to `people.csv` `id` or `name` column                                         |
| contact_id                 |                                                                                         |
| field_name                 | `contact_type`, `comment` and contact type specific parameters                          |
| value                      |                                                                                         |

*Note:* You should use reference to `id` or `email` column, not both together.

##### CSV Entity #7. Ticket

###### tickets.csv

| Column name                | Description                                                                             |
| -------------------------- |-----------------------------------------------------------------------------------------|
| id                         | use as reference for `ticket_messages.csv`, `ticket_custom_fields.csv`                  |
| subject                    |                                                                                         |
| user                       | means a person email                                                                    |
| agent                      | means a person email                                                                    |
| status                     |                                                                                         |
| date_created               | created could be empty (current time)                                                   |
| custom "Custom field name" | could be multiple, see `example/csv_inline/tickets.csv`                                 |

###### ticket_messages.csv

| Column name                | Description                                                                             |
| -------------------------- |-----------------------------------------------------------------------------------------|
| ticket_id                  | reference to `tickets.csv`                                                              |
| message_id                 |                                                                                         |
| message_text               |                                                                                         |
| user                       | means a person email                                                                    |
| date_created               | created could be empty (current time)                                                   |

###### ticket_attachments.csv

| Column name                | Description                                                                             |
| -------------------------- |-----------------------------------------------------------------------------------------|
| message_id                 | reference to `ticket_messages.csv`                                                      |
| person                     | means a person email                                                                    |
| blob_url                   |                                                                                         |
| blob_path                  |                                                                                         |
| file_name                  |                                                                                         |
| content_type               |                                                                                         |
| is_inline                  | boolean                                                                                 |

###### ticket_custom_fields.csv

| Column name                | Description                                                                             |
| -------------------------- |-----------------------------------------------------------------------------------------|
| ticket_id                  | reference to `tickets.csv`                                                              |
| field_name                 |                                                                                         |
| value                      |                                                                                         |

##### CSV Entity #8. Organization

###### organizations.csv

Base info:

| Column name                | Description                                                                             |
| -------------------------- |-----------------------------------------------------------------------------------------|
| id                         | is optional                                                                             |
| name                       |                                                                                         |
| importance                 |                                                                                         |
| date_created               | created could be empty (current time)                                                   |
| blob_url                   |                                                                                         |
| blob_path                  |                                                                                         |
| file_name                  |                                                                                         |
| content_type               |                                                                                         |
| custom "Custom field name" | could be multiple, see `example/csv_inline/organizations.csv`                           |


Contact info:

| Column name                | Description                                                                             |
| -------------------------- |-----------------------------------------------------------------------------------------|
| address                    |                                                                                         |
| city                       |                                                                                         |
| state                      |                                                                                         |
| post_code                  |                                                                                         |
| country                    |                                                                                         |
| facebook                   | Facebook profile URL                                                                    |
| im                         | InstantMessage username                                                                 |
| linkedin                   | LinkedIn profile URL                                                                    |
| mobile                     | Phone Number (unspaced and prefixed with country code e.g. +447700900315, +12025550156) |
| fax                        | Phone Number                                                                            |
| phone                      | Phone Number                                                                            |
| skype                      | Skype username                                                                          |
| twitter                    | Twitter username                                                                        |
| website                    | Website URL                                                                             |

 Example:
 
| name              | importance | blob_url                | file_name   | content_type |
|-------------------|------------|-------------------------|-------------|--------------|
| Some Organization | 1          | http://site.com/img.png | preview.png | image/png    | 

###### organization_contact_data.csv

| Column name                | Description                                                                             |
| -------------------------- |-----------------------------------------------------------------------------------------|
| organization_id            | reference to `organizations.csv` `id` or `name` column                                  |
| contact_id                 |                                                                                         |
| field_name                 | `contact_type`, `comment` and contact type specific parameters                          |
| value                      |                                                                                         |

*Note:* You should use reference to `id` or `email` column, not both together.

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
 
| organization_id   | contact_id | field_name           | value                         |
|-------------------|------------|----------------------|-------------------------------|
| Some Organization | contact_1  | contact_type         | address                       |
| Some Organization | contact_1  | comment              | some comment                  |
| Some Organization | contact_1  | address              | address                       |
| Some Organization | contact_1  | city                 | city                          |
| Some Organization | contact_1  | state                | state                         |
| Some Organization | contact_1  | zip                  | zip                           |
| Some Organization | contact_1  | country              | country                       |

2) "facebook" contact type:

| organization_id   | contact_id | field_name           | value                         |
|-------------------|------------|----------------------|-------------------------------|
| Some Organization | contact_2  | contact_type         | facebook                      |
| Some Organization | contact_2  | comment              | some comment                  |
| Some Organization | contact_2  | profile_url          | https:://facebook.com/profile |

3) "fax" contact type:

| organization_id   | contact_id | field_name           | value                         |
|-------------------|------------|----------------------|-------------------------------|
| Some Organization | contact_3  | contact_type         | fax                           |
| Some Organization | contact_3  | comment              | some comment                  |
| Some Organization | contact_3  | country_calling_code | +7                            |
| Some Organization | contact_3  | number               | 1234567                       |
| Some Organization | contact_3  | type                 | phone                         |

4) "instant_message" contact type:

| organization_id   | contact_id | field_name           | value                         |
|-------------------|------------|----------------------|-------------------------------|
| Some Organization | contact_4  | contact_type         | instant_message               |
| Some Organization | contact_4  | username             | some_user                     |
| Some Organization | contact_4  | service              | service                       |

5) "linked_in" contact type:

| organization_id   | contact_id | field_name           | value                         |
|-------------------|------------|----------------------|-------------------------------|
| Some Organization | contact_5  | contact_type         | linked_in                     |
| Some Organization | contact_5  | profile_url          | https://linkedin.com/profile  |

6) "mobile" contact type:

| organization_id   | contact_id | field_name           | value                         |
|-------------------|------------|----------------------|-------------------------------|
| Some Organization | contact_6  | contact_type         | mobile                        |
| Some Organization | contact_6  | comment              | some comment                  |
| Some Organization | contact_6  | country_calling_code | +7                            |
| Some Organization | contact_6  | number               | 1234567                       |
| Some Organization | contact_6  | type                 | phone                         |

7) "phone" contact type:

| organization_id   | contact_id | field_name           | value                         |
|-------------------|------------|----------------------|-------------------------------|
| Some Organization | contact_7  | contact_type         | phone                         |
| Some Organization | contact_7  | comment              | some comment                  |
| Some Organization | contact_7  | country_calling_code | +7                            |
| Some Organization | contact_7  | number               | 1234567                       |
| Some Organization | contact_7  | type                 | phone                         |

8) "skype" contact type:

| organization_id   | contact_id | field_name           | value                         |
|-------------------|------------|----------------------|-------------------------------|
| Some Organization | contact_8  | contact_type         | skype                         |
| Some Organization | contact_8  | comment              | some comment                  |
| Some Organization | contact_4  | username             | some_user                     |

9) "twitter" contact type:

| organization_id   | contact_id | field_name           | value                         |
|-------------------|------------|----------------------|-------------------------------|
| Some Organization | contact_9  | contact_type         | twitter                       |
| Some Organization | contact_9  | comment              | some comment                  |
| Some Organization | contact_9  | display_feed         | 1                             |

10) "website" contact type:

| organization_id   | contact_id | field_name           | value                         |
|-------------------|------------|----------------------|-------------------------------|
| Some Organization | contact_10 | contact_type         | website                       |
| Some Organization | contact_10 | comment              | some comment                  |
| Some Organization | contact_10 | url                  | http://site.com               |

###### organization_custom_fields.csv

| Column name                | Description                                                                   |
| -------------------------- |-------------------------------------------------------------------------------|
| organization_id            | reference to `organizations.csv` `id` or `name` column                        |
| field_name                 |                                                                               |
| value                      |                                                                               |

*Note:* You should use reference to `id` or `email` column, not both together.

#### Exporter #2. JSON

###### JSON Files structure (input-path)

 - **input_path/**
    - **1/**
        - **articles/**
            - article1.json
            - article2.json
        - **downloads/**
            - download1.json
            - download2.json
        - **feedback/**
            - feedback1.json
            - feedback2.json
        - **news/**
            - news1.json
            - news2.json
        - **organizations/**
            - organization1.json
            - organization2.json
        - **people/**
            - person1.json
            - person2.json
        - **tickets/**
            - ticket1.json
            - ticket2.json
        - [batch.json](#json-batchjson)
            
Example: `app/src/Application/ImportBundle/Resources/example/json`

###### JSON Entity #1. Article

```json
{
  "oid": "1",
  "import_map_key": "zd_article",
  "person": "user@example.com",
  "title": "Article 1",
  "content": "Content 1",
  "language": "english",
  "end_action": null,
  "total_rating": 0,
  "num_comments": 0,
  "num_ratings": 0,
  "view_count": 0,
  "status": "published",
  "date_created": "2015-01-15 00:00:00",
  "date_published": null,
  "date_updated": null,
  "date_end": null,
  "categories": ["Category 1"],
  "labels": ["Label 1", "Label 2", "Label 3"],
  "custom_fields": [
    {
      "oid": "1",
      "key": "Multiple-select box field",
      "value": "Choice 1 > Choice 2"
    }
  ],
  "attachments": [
    {
      "oid": 0,
      "person": "user@example.com",
      "blob_data": null,
      "blob_url": "",
      "blob_path": "\/deskpro\/www\/app\/src\/Application\/ImportBundle\/Resources\/example\/csv\/articles.csv",
      "file_name": "articles.csv",
      "content_type": "csv",
      "is_inline": false
    }
  ],
  "comments": [
    {
      "oid": 0,
      "person_email": "user@example.com",
      "content": "Comment 1",
      "status": "validating",
      "is_reviewed": false,
      "validating": "",
      "date_created": "2015-01-15 00:00:00"
    }
  ],
  "translations": [
    {
      "oid": "0",
      "language": "es_ES",
      "property": "title",
      "value": "Article 1 (es_ES)"
    },
    {
      "oid": "1",
      "language": "es_ES",
      "property": "content",
      "value": "Content 1 (es_ES)"
    }
  ]
}
```

###### JSON Entity #2. Article Category

```json
{
  "oid": "1",
  "title": "Category 1",
  "import_map_key": "zd_article_category",
  "categories": [
    {
      "oid": "1",
      "title": "Sub Category 1",
      "categories": []
    },
    {
      "oid": "2",
      "title": "Sub Category 2",
      "categories": []
    }
  ]
}
```

###### JSON Entity #3. Download

```json
{
  "oid": 1,
  "person": "user@example.com",
  "title": "Download 1",
  "content": "Download Content 1",
  "language": "english",
  "total_rating": 0,
  "num_comments": 0,
  "num_ratings": 0,
  "num_downloads": 0,
  "view_count": 0,
  "category": "Category 1",
  "status": "published",
  "attachment": {
    "oid": 0,
    "person": "user@example.com",
    "blob_data": null,
    "blob_url": "",
    "blob_path": "\/deskpro\/www\/app\/src\/Application\/ImportBundle\/Resources\/example\/csv\/downloads.csv",
    "file_name": "downloads.csv",
    "content_type": "csv",
    "is_inline": false
  },
  "date_created": "2015-03-05 19:40:10",
  "date_published": null,
  "labels": ["Label 1"]
}
```

###### JSON Entity #4. Feedback

```json
{
  "oid": 1,
  "person": "user@example.com",
  "language": "english",
  "title": "Feedback 1",
  "content": "Feedback Content 1",
  "popularity": 10,
  "status": "published",
  "total_rating": 0,
  "num_comments": 0,
  "num_ratings": 0,
  "view_count": 0,
  "category": "Feedback Category 1",
  "labels": ["Feedback Label 1"],
  "date_created": "2015-03-05 19:40:10",
  "date_published": null,
  "attachments": [
    {
      "oid": 0,
      "person": "user@example.com",
      "blob_data": null,
      "blob_url": "",
      "blob_path": "\/deskpro\/www\/app\/src\/Application\/ImportBundle\/Resources\/example\/csv\/feedback.csv",
      "file_name": "feedback.csv",
      "content_type": "csv",
      "is_inline": false
    }
  ],
  "custom_fields": []
}
```

###### JSON Entity #5. News

```json
{
  "oid": 1,
  "person": "user@example.com",
  "language": "english",
  "title": "News Title 1",
  "content": "News Content 1",
  "view_count": 0,
  "total_rating": 0,
  "num_comments": 0,
  "num_ratings": 0,
  "status": "published",
  "date_created": "2015-03-05 19:40:10",
  "date_published": null,
  "category": "News Category 1",
  "labels": ["News Label 1"]
}
```

###### JSON Entity #6. Person

```json
{
  "oid": 710618382,
  "is_agent": true,
  "is_user": false,
  "is_admin": true,
  "first_name": null,
  "last_name": null,
  "name": "Sergey",
  "override_display_name": null,
  "password": null,
  "password_scheme": "plain",
  "timezone": "Europe/Moscow",
  "date_created": "2015-02-06 19:05:35",
  "language": null,
  "organization": null,
  "organization_position": null,
  "emails": ["user@example.com"],
  "labels": ["label1", "label2"],
  "user_groups": [],
  "contact_data": [],
  "custom_fields": []
}
```

###### JSON Entity #7. Ticket

```json
{
  "oid": "1",
  "import_map_key": "zd_ticket",
  "ref": "A4G7I8Y1RV",
  "department": null,
  "person": "user@example.com",
  "agent": "user@example.com",
  "agent_team": null,
  "status": "awaiting_agent",
  "date_created": "2015-02-06 19:03:43",
  "date_resolved": null,
  "date_archived": null,
  "subject": "Subject 1",
  "priority": {
    "oid": 0,
    "title": "high",
    "value": 50
  },
  "language": null,
  "category": null,
  "workflow": null,
  "product": null,
  "organization": null,
  "is_hold": false,
  "urgency": 1,
  "participants": [],
  "labels": [
    "label1",
    "label2"
  ],
  "messages": [
    {
      "oid": 1,
      "person": "user@example.com",
      "date_created": "2015-02-06 19:03:43",
      "message_text": "Reply content",
      "message_html": null,
      "is_note": false,
      "attachments": []
    }
  ],
  "custom_fields": [],
  "log_message": "Imported from ZenDesk"
}
```

###### JSON Entity #8. Organization

```json
{
  "oid": "Some Organization",
  "name": "Some Organization",
  "picture": null,
  "importance": 5,
  "date_created": "2015-07-30 06:09:01",
  "contact_data": [
    {
      "oid": 1,
      "contact_type": "mobile",
      "comment": "some comment",
      "country_calling_code": "country_calling_code",
      "number": "number",
      "type": "type"
    }
  ],
  "custom_fields": [],
  "labels": [
    "label1",
    "label2"
  ]
}
```

###### JSON Batch.json

```json
{
  "batch_id" : 1,
  "date_created": "2015-07-30 06:09:01"
}
```

#### Exporter #3. OsTicket

Exports data from OsTicket database.

##### OsTicket Configuration

Add `osticket_import` configuration to `config.php`

```php
$DP_CONFIG['osticket_import'] = array(
    'db_host'       => 'localhost',
    'db_port'       => 3306, // (ignore if default value)
    'db_name'       => 'os_ticket',
    'db_username'   => 'root',
    'db_password'   => 'deskpro'
);
```

##### OsTicket Fixtures

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

#### Exporter #4. ZenDesk

Exports data from ZenDesk account. Supported tickets and people data export.

##### ZenDesk Configuration

Add `zendesk_import` configuration to `config.php`

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

##### ZenDesk Fixtures


###### ZenDesk Fixtures Core API

Create fake users:

```bash
php cmd.php dpdev:import:fixtures --type=person
```

Create fake tickets:

```bash
php cmd.php dpdev:import:fixtures --type=ticket
```

###### ZenDesk Fixtures Help Center

Create fake help center categories:

```bash
php cmd.php dpdev:import:fixtures --type=category
```

Create fake help center sections (sub categories):

```bash
php cmd.php dpdev:import:fixtures --type=section
```

Create fake articles (includes comments, attachments and translations):

```bash
php cmd.php dpdev:import:fixtures --type=article
```

You can clear all category related data just by removing category, every section and all articles in the category will also be deleted:

```bash
php cmd.php dpdev:import:fixtures --type=category -d
```

##### ZenDesk Batch.json

```json
{
  "batch_id" : 1,
  "date_created": "2015-07-30 06:09:01",
  "people_end_time": "2015-06-30 08:00:01",
  "tickets_end_time": "2015-07-30 06:09:01",
  "articles_end_time":"2015-07-30 07:09:01",
  "retry_after_time": "2015-07-30 06:11:01",
  "has_remaining": true
}
```

#### Exporter #5. DeskPRO

Add `zendesk_import` configuration to `config.php`

```php
$DP_CONFIG['deskpro_import'] = array(
    'db_host'         => 'host',
    'db_port'         => 3306, // (ignore if default value)
    'db_name'         => 'dbname',
    'db_username'     => 'dbuser',
    'db_password'     => 'dbpassword',
    'start_ticket_id' => 0,
);
```