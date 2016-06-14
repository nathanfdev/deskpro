To regenerate these docs:

1) cd to app/ dir

2) Run: ./vendor/bin/swagger /path/to/deskpro/app/src/Application/ApiBundle/Controller --output /some/dir/here

4) Delete the index.php file generated

5) In api-docs.json, remove the ".{format}" suffix that was added (find+replace)

6) Rename api-docs.json to deskpro-api.json

7) Move all files to app/src/Application/ApiBundle/Resources/views/SwaggerDocs

8) Verify it all still works by viewing /api/ in your browser.