<?php

return [
    'install' => [
        'handler' => 'deskpro_magento\\InstallerHandler',
    ],
    'api' => [
        'package_request_handler' => 'deskpro_magento\\RequestHandler\\PackageRequestHandler',
    ],
];
