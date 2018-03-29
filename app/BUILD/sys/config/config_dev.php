<?php

if (!defined('DP_ROOT')) {
    exit('No access');
}
$loader->import(DP_ROOT.'/sys/config/config.php');

$container->setParameter('kernel.debug', true);
$container->loadFromExtension('twig', [
    'debug' => true,
]);
