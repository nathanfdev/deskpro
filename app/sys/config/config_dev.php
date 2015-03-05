<?php if (!defined('DP_ROOT')) exit('No access');
$loader->import(DP_ROOT.'/sys/config/config.php');

$container->setParameter('kernel.debug', true);
$container->loadFromExtension('twig', array(
    'debug' => true
));

// Enable logger in dev mode
$container->loadFromExtension('monolog', array(
    'handlers' => array(
        'main' => array(
            'type'  => 'stream',
            'path'  => dp_get_log_dir()."/deskpro.dev.log",
            'level' => 'debug',
        ),
    )
));