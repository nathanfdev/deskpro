<?php

return [
    'services' => [
        [
            'id'    => 'ms_translator',
            'class' => 'deskpro_ms_translator\\DependencyInjection\\MsTranslatorService',
        ],
    ],

    'agent' => [
        'request_handler' => 'deskpro_ms_translator\\AgentRequestHandler',
    ],
];
