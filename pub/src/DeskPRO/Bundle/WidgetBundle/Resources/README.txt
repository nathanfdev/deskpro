DEV INSTRUCTIONS
------------------------------------------------------------------------------------------------------------------------

1. Run the bundle command once to initi the loader file:

    cd pub/
    npm run-script gulp bundle:widget

The 'loader' is what we will give customers to copy+paste onto their websites. Note: If you update widget_loader.js,
you will need to re-runt he bundle task (because it's not part of our usual webpack process; it's not watched).

This will be minified and copy+pasted directly onto a website. But while developing, it's useful to just hot-link it
directly. Like this:

2. Create a test HTML page with the following code:

    <html>
    <head>
    <script>window.__DP_APP_SRC__ = 'http://localhost:9666/pub/build/DeskPRO_WidgetBundle.js';</script>
    <script type="text/javascript" src="http://your-localhost/pub/build/widget_loader.js"></script>
    </head>
    <body>

    <h1>DeskPRO Widget Test</h1>

    </body>
    </html>

3. Start the webpack server:

    npm run-script gulp dev:widget

4. Open that HTML page in your browser.



NOTES
------------------------------------------------------------------------------------------------------------------------

* Use the HTML test page on a DIFFERENT DOMAIN than DeskPRO is run from. This is to ensure we get cross-domain
  functions working as expected. For example, put the test page on deskpro-html-test.localhost, and then maybe
  you have DeskPRO itself on deskpro.localhost.
