<?php

return [
    'install' => [
        'handler' => 'deskpro_us_ezpublish\\InstallerHandler',
    ],
    'api' => [
        'package_request_handler' => 'deskpro_us_ezpublish\\RequestHandler\\PackageRequestHandler',
    ],
];
