<?php

return [
    'api' => [
        'package_request_handler' => 'deskpro_slack\\RequestHandler\\PackageRequestHandler',
    ],
    'install' => [
        'handler' => 'deskpro_slack\\InstallerHandler',
    ],
];
