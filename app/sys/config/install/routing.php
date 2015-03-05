<?php if (!defined('DP_ROOT')) exit('No access');

use Symfony\Component\Routing\RouteCollection;

$collection = new RouteCollection();

$col = $loader->import(DP_ROOT.'/src/Application/InstallBundle/Resources/config/install-routing.php');
$col->addPrefix('/install');
$collection->addCollection($col);

return $collection;
