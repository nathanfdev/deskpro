<?php

require_once DP_ROOT.'/vendor/symfony/src/Symfony/Framework/UniversalClassLoader.php';

$loader = new Symfony\Framework\UniversalClassLoader();
$loader->registerNamespaces(array(
	'DeskPRO'                    => DP_ROOT.'/src/DeskPRO',
	'Application'                => DP_ROOT.'/src',
    'Bundle'                     => DP_ROOT.'/src',

    'Symfony'                    => DP_ROOT.'/vendor/symfony/src',
    'Doctrine\\Common'           => DP_ROOT.'/vendor/doctrine-common/lib',
    'Doctrine\\DBAL\\Migrations' => DP_ROOT.'/vendor/doctrine-migrations/lib',
    'Doctrine\\DBAL'             => DP_ROOT.'/vendor/doctrine-dbal/lib',
    'Doctrine'                   => DP_ROOT.'/vendor/doctrine/lib',
    'Zend'                       => DP_ROOT.'/vendor/zend/library',
));
$loader->registerPrefixes(array(
    'Swift_' => DP_ROOT.'/vendor/swiftmailer/lib/classes',
    'Twig_'  => DP_ROOT.'/vendor/twig/lib',
));
$loader->register();
