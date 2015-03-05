<?php return array(
    'install' => array(
        'handler' => 'deskpro_us_ldap\\InstallerHandler'
    ),
    'api' => array(
        'package_request_handler' => 'deskpro_us_ldap\\RequestHandler\\PackageRequestHandler'
    )
);
