<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage InstallBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\InstallBundle\Data;

use Doctrine\ORM\EntityManager;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class DataInitializer
{
	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	protected $container;

	public function __construct(DeskproContainer $container)
	{
		$this->container = $container;
	}

	public function run()
	{
		$this->runStyleInit();
		$this->runSearchIndex();
	}

	public function runStyleInit()
	{
		$style = $this->container->getEm()->find('DeskPRO:Style', 1);

		$css_source = file_get_contents(DP_WEB_ROOT . '/web/stylesheets/user/main.css');
		$css = new \Application\DeskPRO\Style\UserStyle($css_source);

		$desc = $this->container->getFilestorage()->createRandomPath();
		$desc->write($css->compileCss(), array(
			'content_type' => 'text/css',
			'filename' => 'main.css'
		));
		$blob = $this->container->getEm()->find('DeskPRO:Blob', $desc->getPath());
		$style->css_blob = $blob;

		$this->container->getEm()->persist($style);
		$this->container->getEm()->flush();
	}

	public function runSearchIndex()
	{
		$types = array(
			array('article',   'articles',  'DeskPRO:Article'),
			array('download',  'downloads', 'DeskPRO:Download'),
			array('feedback',  'feedback',  'DeskPRO:Feedback'),
			array('news',      'news',      'DeskPRO:News'),
		);

		foreach ($types as $t) {
			list ($content_type, $table, $entity) = $t;
			$all_ids = $this->container->getDb()->fetchAllCol("SELECT id FROM $table ORDER BY id ASC");
			$batch = $this->container->getEm()->getRepository($entity)->getByIds($all_ids);
			if ($batch) {
				$this->container->getSearchAdapter()->updateObjectsInIndex($batch);
			}
		}
	}
}
