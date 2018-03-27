<?php

/**
 * DeskPRO.
 */

namespace Application\AdminInterfaceBundle\Controller;

use Application\DeskPRO\App\Package\PackageInstaller;
use Application\DeskPRO\Entity\AppPackage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class AppsController extends AbstractController
{
    /**
     * creates and outputs AppPackage as *.zip.
     *
     * @param Request $request
     * @param $name
     *
     * @throws NotFoundHttpException
     * @throws \Exception
     *
     * @return BinaryFileResponse
     */
    public function downloadPackageAction(Request $request, $name)
    {
        $rep = $this->em->getRepository('DeskPRO:AppPackage');
        /** @var AppPackage $package */
        if (!$package = $rep->findOneBy(['name' => $name, 'native_name' => null])) {
            throw new NotFoundHttpException();
        }

        $tmpdir = dp_get_tmp_dir().DIRECTORY_SEPARATOR.$package['name'].'-'.mt_rand(1000, 9999);
        if (!@mkdir($tmpdir)) {
            throw new \Exception('Failed to create extraction directory');
        }

        $path      = realpath($tmpdir);
        $installer = new PackageInstaller(
            $this->container->getEm(),
            $this->container->getBlobStorage(),
            $this->container->getImagine()
        );

        $installer->dumpPackage($package, $path);

        // compress
        /** @var \Orb\Zip\Zip $zipper */
        $zipper = $this->container->getSystemService('zipper');
        $file   = $path.'/'.$package['name'].'.zip';
        $zipper->compressPath($path, $file);
        $response = new BinaryFileResponse($file);

        // cleanup
        $this->container->getEventDispatcher()->addListener(
            KernelEvents::TERMINATE,
            function (PostResponseEvent $event) use ($path, $file) {
                function rrmdir($dir)
                {
                    if (is_dir($dir)) {
                        $objects = scandir($dir);
                        foreach ($objects as $object) {
                            if ($object != '.' && $object != '..') {
                                if (filetype($dir.'/'.$object) == 'dir') {
                                    rrmdir($dir.'/'.$object);
                                } else {
                                    unlink($dir.'/'.$object);
                                }
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
