Export run:
php cmd.php dp:export:check csv --input-path="app/src/Application/ImportBundle/Resources/docs/data_example/csv" --verbose
php cmd.php dp:export:run csv --input-path="app/src/Application/ImportBundle/Resources/docs/data_example/csv" --output-path="app/src/Application/ImportBundle/Resources/docs/data_example/csv_to_json" --verbose

Import run:
php cmd.php dp:import:run csv --input-path="app/src/Application/ImportBundle/Resources/docs/data_example/csv" --verbose