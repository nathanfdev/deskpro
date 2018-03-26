<?php

return [
    'install' => [
        'handler' => 'deskpro_us_vbulletin\\InstallerHandler',
    ],
    'api' => [
        'package_request_handler' => 'deskpro_us_vbulletin\\RequestHandler\\PackageRequestHandler',
    ],
];
