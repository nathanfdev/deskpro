<?php

return [
    'install' => [
        'handler' => 'deskpro_us_phpbb\\InstallerHandler',
    ],
    'api' => [
        'package_request_handler' => 'deskpro_us_phpbb\\RequestHandler\\PackageRequestHandler',
    ],
];
