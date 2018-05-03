<?php

return [
    'install' => [
        'handler' => 'deskpro_us_active_directory\\InstallerHandler',
    ],
    'api' => [
        'package_request_handler' => 'deskpro_us_active_directory\\RequestHandler\\PackageRequestHandler',
    ],
];
