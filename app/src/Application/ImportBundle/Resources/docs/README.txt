Importer tool

=====================

Commands:

    1) Export check

        Reads data from an external source (csv, json, osticket, zendesk) and validates that all entities has requires fields and they have valid values.
        Import check is not necessary, use dp:export:check json to check json files.

        php cmd.php dp:export:check exporter_type [--input-path=] [--verbose]

        exporter_type
            Available values: csv, json, osticket, zendesk

        --input-path=
            Csv and json exporter types need input path to be specified.

        --verbose
            Shows log output in the console.

        Examples:
        php cmd.php dp:export:check csv --input-path="app/src/Application/ImportBundle/Resources/docs/data_example/csv" --verbose
        php cmd.php dp:export:check json --input-path="app/src/Application/ImportBundle/Resources/docs/data_example/csv_to_json" --verbose


    2) Export run

        Reads data from an external source, validate entities and write them to json files.
        Export could be run in json files only.

        php cmd.php dp:export:run exporter_type [--input-path=] [--output-path=""] [--verbose] [--dry-run]

        exporter_type
            Available values: csv, json, osticket, zendesk

        --input-path=
            Csv and json exporter types need input path to be specified.

        --output-path=
            Destination path of generating json files.

        --verbose
            Shows log output in the console.

        --dry-run
            Test run, json files are not generated. If dry run mode is enabled "output-path" is not required.

        Examples:
        php cmd.php dp:export:run csv --input-path="app/src/Application/ImportBundle/Resources/docs/data_example/csv" \
            --output-path="app/src/Application/ImportBundle/Resources/docs/data_example/csv_to_json" --verbose


    3) Import run

        Reads data from an external source, validate entities and import them to deskpro database.

        php cmd.php dp:import:run exporter_type [--input-path=] [--verbose] [--dry-run]

        exporter_type
            Available values: csv, json, osticket, zendesk

        --input-path=
            Csv and json exporter types need input path to be specified.

        --verbose
            Shows log output in the console.

        --dry-run
            Test run, entities are not imported to deskpro database.

        Examples:
        php cmd.php dp:import:run csv --input-path="app/src/Application/ImportBundle/Resources/docs/data_example/csv" --verbose
        php cmd.php dp:import:run json --input-path="app/src/Application/ImportBundle/Resources/docs/data_example/csv_to_json" --verbose


=====================

Exporters:

    1) Csv

        Exports data from CSV files.

        Files
         - articles.csv
         - downloads.csv
         - feedback.csv
         - news.csv
         - people.csv
         - tickets.csv
         - ticket_messages.csv
         - ticket_attachments.csv

        Example files dir:
        app/src/Application/ImportBundle/Resources/docs/data_example/csv

         - Articles required columns

            'person',
            'title',
            'content',
            'slug',
            'language',
            'status',
            'category',
            'label',
            'date_created'

            Person means a person email
            Slug could be empty (generated from title)
            Date created could be empty (current time)
            Label could be empty (only one label is supported)
            Category could be empty


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


        - Feedback required columns

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
            'date_published'

            Person means a person email
            Slug could be empty (generated from title)
            Date created could be empty (current time)
            Date published could be empty
            Label could be empty (only one label is supported)
            Category could be empty


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


        - People required columns

            'name',
            'email'

            'is_agent' is optional


        - Tickets required columns

            'id',
            'subject',
            'user',
            'agent',
            'status',
            'date_created'

            User means a user email
            Agent means a agent email
            Date created could be empty (current time)


        - Ticket messages required columns

            'ticket_id',
            'message_id',
            'message_text',
            'user'

            User means a user email


        - Ticket attachments required columns

            'person',
            'blob_url',
            'blob_path',
            'file_name',
            'content_type',
            'is_inline'

            Person means a person email