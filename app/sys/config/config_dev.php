<?php if (!defined('DP_ROOT')) exit('No access');
$loader->import(DP_ROOT.'/sys/config/config.php');

$container->setParameter('kernel.debug', true);
$container->loadFromExtension('twig', array(
    'debug' => true
));

$container->loadFromExtension('monolog', array(
    'handlers' => array(
        'main' => array(
            'type' => 'stream',
            'path' => "@=container.getLogDir() ~ '/deskpro.log'"
        )
    )
));