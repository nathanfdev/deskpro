<?php return array(
    'install' => array(
        'handler' => 'deskpro_ticket_hostnames\\InstallerHandler'
    ),
    'agent' => array(
        'request_handler' => 'deskpro_ticket_hostnames\\RequestHandler\\AgentRequestHandler'
    )
);
