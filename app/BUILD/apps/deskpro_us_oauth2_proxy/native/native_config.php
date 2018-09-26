<?php

return [
    'install' => [
        'handler' => 'deskpro_us_oauth2_proxy\\InstallerHandler',
    ],
    'api' => [
        'package_request_handler' => 'deskpro_us_oauth2_proxy\\RequestHandler\\PackageRequestHandler',
    ],
];
