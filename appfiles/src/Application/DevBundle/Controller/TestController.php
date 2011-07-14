<?php

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ClientMessage;

use Orb\Util\Strings;

class TestController extends Controller
{
    public function indexAction()
    {
		$db = App::getDb();
		$em = App::getOrm();

		$db->exec("TRUNCATE TABLE downloads");
		$db->exec("TRUNCATE TABLE download_categories");
		$db->exec("TRUNCATE TABLE download_category_permissions");

		$dlinfo = array(
			array(
				'title' => 'Patches',
				'children' => array(
					array(
						'title' => 'DeskPRO v3.4',
						'files' => array(
							array('title' => 'Patch 1 for 3.4.2', 'filename' => 'v3_4_2_P1.zip')
						)
					),
					array(
						'title' => 'DeskPRO v3.3',
						'files' => array(
							array('title' => 'Patch 1 for 3.3.1', 'filename' => 'v3_3_1_P1.zip'),
							array('title' => 'Patch 2 for 3.3.1', 'filename' => 'v3_3_1_P2.zip'),
						)
					)
				)
			),
			array(
				'title' => 'Documentation',
				'files' => array(
					array('title' => 'Installation Guide', 'filename' => 'DeskPRO-Install-Guide.pdf'),
					array('title' => 'Administration Guide', 'filename' => 'DeskPRO-Admin-Guide.pdf'),
					array('title' => 'Usage Guide', 'filename' => 'DeskPRO-User-Guide.pdf'),
					array('title' => 'Style Guide', 'filename' => 'DeskPRO-Style-Guide.pdf'),
				)
			),
			array(
				'title' => 'Tools',
				'files' => array(
					array('title' => 'Reset Admin Password', 'filename' => 'tool-reset-admin-pass.zip'),
					array('title' => 'Regenerate All Passwords', 'filename' => 'tool-regenerate-passwords.zip'),
					array('title' => 'Schema Checker', 'filename' => 'tool-schema-cheker.zip'),
					array('title' => 'Usage Logger', 'filename' => 'usage-logger.zip'),
				)
			),
		);

		$agent = $em->find('DeskPRO:Person', 20001);

		$em->beginTransaction();

		$proc = function ($dlinfo, $parent = null) use ($em, &$proc) {
			foreach ($dlinfo as $info) {
				$cat = new \Application\DeskPRO\Entity\DownloadCategory();
				$cat['title'] = $info['title'];
				if ($parent) {
					$cat['parent'] = $parent;
				}

				$em->persist($cat);

				if (!empty($info['files'])) {
					foreach ($info['files'] as $fileinfo) {
						$file = new \Application\DeskPRO\Entity\Download();

						$blob = new \Application\DeskPRO\Entity\Blob();
						$blob->fromArray(array(
							'filename' => $fileinfo['filename'],
							'filesize' => mt_rand(1000, 10000),
						));

						$file->fromArray(array(
							'blob' => $blob,
							'category' => $cat,
							'title' => $fileinfo['title'],
							'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc faucibus suscipit sem, sit amet ullamcorper libero ultrices eu. Quisque quis mauris dui, at sagittis mauris. Vestibulum sed libero erat, ut pellentesque lorem. Nullam eu risus mi, eget laoreet erat. Morbi cursus commodo mi, placerat tempus lectus imperdiet non. Nulla facilisi. Vivamus dignissim eros sed mauris placerat viverra. Pellentesque blandit mollis tortor, et feugiat massa suscipit non. Vivamus libero tellus, euismod semper porttitor sit amet, egestas et ligula. Mauris vulputate, felis adipiscing auctor lacinia, est leo ornare ante, ac facilisis leo nulla et nisl. Suspendisse potenti. Cras ac elit sapien. Proin semper dui non diam fringilla scelerisque',
							'num_downloads' => mt_rand(0,100),
						));

						$em->persist($blob);
						$em->persist($file);
					}
				}

				if (!empty($info['children'])) {
					$proc($info['children'], $cat);
				}
			}
		};

		$proc($dlinfo);

		$em->flush();
		$em->commit();

		exit;
		return $this->render('DevBundle:Test:test.html.twig');
    }
}
