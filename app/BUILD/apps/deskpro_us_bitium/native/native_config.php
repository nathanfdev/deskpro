<?php

return [
    'install' => [
        'handler' => 'deskpro_us_bitium\\InstallerHandler',
    ],
    'api' => [
        'package_request_handler' => 'deskpro_us_bitium\\RequestHandler\\PackageRequestHandler',
    ],
];
