<?php if (!defined('DP_ROOT')) {
    exit('No access');
} ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>DeskPRO</title>
    <link rel="stylesheet" type="text/css" href="./web/stylesheets/install/install.css" />
</head>
<body>

<div style="margin: 100px;">
    <h2>URL Routing Problem</h2>
    <p>
        DeskPRO has detected a problem with URL routing that is resulting in a redirection loop.
    </p>
    <p>
        This usually means you have enabled "clean URLs" (also known as "URL rewriting"), but your server is not handling it properly.
    </p>

    <br/>

    <br/><br/>

    <h3>Fixing your server</h3>
    <p>
        If you want to keep clean URLs, you will need to fix your server. Refer to the DeskPRO knowledgebase for more information:
        <a href="https://support.deskpro.com/kb/articles/177" target="_blank">https://support.deskpro.com/kb/articles/177</a>
    </p>
</div>

</body>
</html>
