<?php

return [
    'install' => [
        'handler' => 'deskpro_ticket_hostnames\\InstallerHandler',
    ],
    'agent' => [
        'request_handler' => 'deskpro_ticket_hostnames\\RequestHandler\\AgentRequestHandler',
    ],
];
