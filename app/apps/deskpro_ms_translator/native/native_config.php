<?php return array(
    'services' => array(
        array(
            'id'    => 'ms_translator',
            'class' => 'deskpro_ms_translator\\DependencyInjection\\MsTranslatorService'
        )
    ),

    'agent' => array(
        'request_handler' => 'deskpro_ms_translator\\AgentRequestHandler'
    )
);
