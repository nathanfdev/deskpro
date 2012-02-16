<?php

namespace Application\DevBundle\Controller;

use Application\DeskPRO\Build\VersionReader;
use Application\DeskPRO\Build\Upgrader;

use Application\DeskPRO\App;

class ModelsController extends \Application\DeskPRO\HttpKernel\Controller\Controller
{
	public function indexAction()
	{
		$em = $this->get('doctrine.orm.entity_manager');
		$all_metadata = $em->getMetadataFactory()->getAllMetadata();

		return $this->render('DevBundle:Models:index.html.php', array(
			'all_metadata' => $all_metadata,
		));
	}

	public function getSqlAction()
	{
		$model = '';
		$all_sql = false;
		if (!empty($_GET['model'])) {
			$model = $_GET['model'];

			if (strpos($model, ':') === false) {
				$model = 'DeskPRO:' . $model;
			}

			$em = $this->container->get('doctrine.orm.entity_manager');
			$metadata = $em->getMetadataFactory()->getMetadataFor($model);
			$tool = new \Doctrine\ORM\Tools\SchemaTool($em);
			$all_sql = $tool->getCreateSchemaSql(array($metadata));
		} else {
			$em = $this->container->get('doctrine.orm.entity_manager');
			$metadata = $em->getMetadataFactory()->getAllMetadata();
			$tool = new \Doctrine\ORM\Tools\SchemaTool($em);
			$all_sql = $tool->getCreateSchemaSql($metadata);

			//$data_reader = new \Application\DeskPRO\Install\InstallData('other_tables.sql');
			//foreach ($data_reader as $sql) {
			//	$all_sql[] = $sql;
			//}

			//$data_reader = new \Application\DeskPRO\Install\InstallData('triggers.sql');
			//foreach ($data_reader as $sql) {
			//	$all_sql[] = $sql;
			//}
		}

		if (!empty($_GET['get_php'])) {
			foreach ($all_sql as &$s) {
				$s = "\$queries[] = \"" . addslashes($s) . "\";";
			}
		} else {
			foreach ($all_sql as &$s) {
				$s = $s . ';';
			}
		}

		return $this->render('DevBundle:Models:get-sql.html.php', array(
			'model' => $model,
			'all_sql' => $all_sql,
		));
	}

	public function regenerateProxiesAction()
	{
		$warmer = new \Symfony\Bundle\DoctrineBundle\CacheWarmer\ProxyCacheWarmer(App::getContainer());
		$warmer->warmUp(null /* doctrine has its own config for cache dir */);

		return $this->render('DevBundle:Models:regenerate-proxies-done.html.php', array(
		));
	}
}
