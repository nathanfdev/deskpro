<?php

return [
    'install' => [
        'handler' => 'deskpro_zapier\\InstallerHandler',
    ],
    'api' => [
        'package_request_handler' => 'deskpro_zapier\\RequestHandler\\PackageRequestHandler',
    ],
];
