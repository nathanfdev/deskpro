<?php

return [
    'install' => [
        'handler' => 'deskpro_us_okta\\InstallerHandler',
    ],
    'api' => [
        'package_request_handler' => 'deskpro_us_okta\\RequestHandler\\PackageRequestHandler',
    ],
];
