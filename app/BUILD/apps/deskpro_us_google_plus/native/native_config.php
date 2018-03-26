<?php

return [
    'install' => [
        'handler' => 'deskpro_us_google_plus\\InstallerHandler',
    ],
    'api' => [
        'package_request_handler' => 'deskpro_us_google_plus\\RequestHandler\\PackageRequestHandler',
    ],
];
