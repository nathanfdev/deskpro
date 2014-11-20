<?php return array(
    'install' => array(
        'handler' => 'deskpro_us_db\\InstallerHandler',
    ),
    'api' => array(
        'package_request_handler' => 'deskpro_us_db\\RequestHandler\\PackageRequestHandler',
    ),
);
