Importer tool

Commands:

1) Export check: php cmd.php dp:export:check exporter_type [--input-path=] [--verbose] [--dry-run]
Reads data from an external source (csv, json, osticket, zendesk) and validates that all entities fields are defined.

Exporter types: csv, json, osticket, zendesk
Csv and json exporter types need input path to be specified.

Examples:
php cmd.php dp:export:check csv --input-path="app/src/Application/ImportBundle/Resources/docs/data_example/csv" --verbose
php cmd.php dp:export:check json --input-path="app/src/Application/ImportBundle/Resources/docs/data_example/csv_to_json" --verbose


Export run:
php cmd.php dp:export:run csv --input-path="app/src/Application/ImportBundle/Resources/docs/data_example/csv" --output-path="app/src/Application/ImportBundle/Resources/docs/data_example/csv_to_json" --verbose

Import run:
php cmd.php dp:import:run csv --input-path="app/src/Application/ImportBundle/Resources/docs/data_example/csv" --verbose
php cmd.php dp:import:run json --input-path="app/src/Application/ImportBundle/Resources/docs/data_example/csv_to_json" --verbose