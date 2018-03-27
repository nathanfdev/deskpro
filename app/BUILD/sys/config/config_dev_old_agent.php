<?php

//// TEMPORARY FILE (only needed until we get rid of the agent interface)

if (!defined('DP_ROOT')) {
    exit('No access');
}
$loader->import(DP_ROOT.'/sys/config/config.php');

$container->setParameter('kernel.debug', true);
$container->loadFromExtension('twig', [
    'debug' => true,
]);
