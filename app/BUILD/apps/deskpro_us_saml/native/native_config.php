<?php

return [
    'install' => [
        'handler' => 'deskpro_us_saml\\InstallerHandler',
    ],
    'api' => [
        'package_request_handler' => 'deskpro_us_saml\\RequestHandler\\PackageRequestHandler',
    ],
];
