<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
* DeskPRO
*
* @package DeskPRO
*/

namespace Application\AdminInterfaceBundle\Controller;

use Application\DeskPRO\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AppsController extends AbstractController
{
	/**
	 * creates and outputs AppPackage as *.zip
	 * @param Request $request
	 * @param $name
	 * @return BinaryFileResponse
	 * @throws NotFoundHttpException
	 * @throws \Exception
	 */
	public function downloadPackageAction(Request $request, $name)
	{
		$rep = $this->em->getRepository('DeskPRO:AppPackage');
		if (!$package = $rep->findOneBy(array('name' => $name, 'native_name' => null))) {
			throw new NotFoundHttpException;
		}

		$tmpdir = dp_get_tmp_dir() . DIRECTORY_SEPARATOR . $package['name'] . '-' . mt_rand(1000,9999);
		if (!@mkdir($tmpdir)) {
			throw new \Exception('Failed to create extraction directory');
		}

		$path = realpath($tmpdir);
		$storage = $this->container->getBlobStorage();

		// copy package files
		foreach ($package->assets as $asset) {
			/** @var $asset AppAsset */
			$filename = $path . '/' . $asset['name'];
			$dir = pathinfo($filename, PATHINFO_DIRNAME);
			!file_exists($dir) && mkdir($dir, 0777, true);
			$storage->copyBlobRecordToFile($filename, $asset->blob);
		}

		// create manifest.json
		file_put_contents($path . '/manifest.json', json_encode($package->getManifest()));


		// compress
		/** @var \Orb\Zip\Zip $zipper */
		$zipper = $this->container->getSystemService('zipper');
		$file = $path . '/' . $package['name'] . '.zip';
		$zipper->compressPath($path, $file);
		$response = new BinaryFileResponse($file);


		// cleanup
		$this->container->getEventDispatcher()->addListener(
			KernelEvents::TERMINATE,
			function(PostResponseEvent $event) use ($path, $file){
				function rrmdir($dir) {
					if (is_dir($dir)) {
						$objects = scandir($dir);
						foreach ($objects as $object) {
							if ($object != "." && $object != "..") {
								if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
							}
						}
						reset($objects);
						rmdir($dir);
					}
				}
				rmdir($path);
				unlink($file);
			}
		);

		return $response;
	}
}