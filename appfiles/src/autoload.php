<?php

require_once DP_ROOT.'/src/vendor/Symfony/src/Symfony/Framework/UniversalClassLoader.php';

$loader = new Symfony\Framework\UniversalClassLoader();
$loader->registerNamespaces(array(
	'DeskPRO'                    => DP_ROOT.'/src/DeskPRO',

    'Symfony'                    => DP_ROOT.'/src/vendor/symfony/src',
    'Application'                => DP_ROOT.'/src',
    'Bundle'                     => DP_ROOT.'/src',
    'Doctrine\\Common'           => DP_ROOT.'/src/vendor/doctrine/lib/vendor/doctrine-common/lib',
    'Doctrine\\DBAL\\Migrations' => DP_ROOT.'/src/vendor/doctrine-migrations/lib',
    'Doctrine\\ODM\\MongoDB'     => DP_ROOT.'/src/vendor/doctrine-mongodb/lib',
    'Doctrine\\DBAL'             => DP_ROOT.'/src/vendor/doctrine/lib/vendor/doctrine-dbal/lib',
    'Doctrine'                   => DP_ROOT.'/src/vendor/doctrine/lib',
    'Zend'                       => DP_ROOT.'/src/vendor/zend/library',
));
$loader->registerPrefixes(array(
    'Swift_' => DP_ROOT.'/src/vendor/swiftmailer/lib/classes',
    'Twig_'  => DP_ROOT.'/src/vendor/twig/lib',
));
$loader->register();
