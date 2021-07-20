<?php

/**
 * DeskPRO.
 */

namespace Application\AdminInterfaceBundle\Controller;

use Application\DeskPRO\App\Package\PackageInstaller;
use Application\DeskPRO\Entity\AppPackage;
use DeskPRO\Component\Filesystem\SafeFile;
use DeskPRO\Component\Filesystem\TmpDir;
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

        $tmpdir = TmpDir::makeTmpDir();

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
        $file   = $path.'/app.zip';
        $zipper->compressPath($path, $file);
        return new BinaryFileResponse($file);
    }
}
