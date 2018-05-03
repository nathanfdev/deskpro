<?php

return [
    'install' => [
        'handler' => 'deskpro_us_onelogin\\InstallerHandler',
    ],
    'api' => [
        'package_request_handler' => 'deskpro_us_onelogin\\RequestHandler\\PackageRequestHandler',
    ],
];
