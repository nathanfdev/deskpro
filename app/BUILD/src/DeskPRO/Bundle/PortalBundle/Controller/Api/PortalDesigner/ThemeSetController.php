<?php

namespace DeskPRO\Bundle\PortalBundle\Controller\Api\PortalDesigner;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\PortalBundle\Controller\Api\AbstractApiController;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\ZipType;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ThemeSetController.
 */
class ThemeSetController extends AbstractApiController
{
    use HelperMethods;

    /**
     * @Route("/portal/api/style/custom-theme-sets")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getCustomThemeSetsAction()
    {
        $brand = $this->container->get('brand_stack')->getActive()->getBrand();

        $qb = $this->getManager()->createQueryBuilder();
        $qb
            ->select('t')
            ->from(ThemeSet::class, 't')
            ->leftJoin(Brand::class, 'b', 'WITH', 'b.edit_theme_set = t.id')
            ->where(
                't.brand = :current_brand',
                'b.id IS NULL',
                'NOT (t.theme_id = \'helpcenter\' AND t.title IS NULL)'
            )
            ->setParameter('current_brand', $brand)
        ;

        if ($brand->getThemeSet()->getThemeId() === 'helpcenter') {
            $qb->andWhere('t.theme_id = \'helpcenter\'');
        }

        $themeSets = $qb->getQuery()->getResult();

        // Standard and sidebar theme are not added in the view anymore we need to re add them for legacy installs
        return new View($themeSets);
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/info")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getThemeSetAction()
    {
        return new View($this->getEditThemeSet());
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/info")
     * @Method({"PUT"})
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function setThemeSetAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if (!array_key_exists('id', $data)) {
            throw new BadRequestHttpException('Request body must contain "id" property');
        }

        $em    = $this->getManager();
        /** @var Brand $brand */
        $brand = $this->container->get('brand_stack')->getActive()->getBrand();

        if (is_numeric($data['id'])) {
            $themeSet = $em->getRepository(ThemeSet::class)->find($data['id']);
            if (!$themeSet) {
                throw $this->createNotFoundException();
            }
        } else {
            $themeId = $data['id'];

            if (!in_array($themeId, ['standard', 'sidebar', 'helpcenter'])) {
                throw new BadRequestHttpException('Invalid theme_id option');
            }

            $qb = $em->createQueryBuilder();
            $qb
                ->select('t')
                ->from(ThemeSet::class, 't')
                ->where('t.brand = :brand')
                ->andWhere('t.theme_id = :theme_id')
                ->andWhere('t.id != :current_theme')
                ->setMaxResults(1)
                ->setParameter('brand', $brand)
                ->setParameter('theme_id', $themeId)
                ->setParameter('current_theme', $brand->getThemeSet())
            ;

            $themeSet = $qb->getQuery()->getOneOrNullResult();

            if (!$themeSet) {
                $themeSet = new ThemeSet();
                $themeSet->setThemeId($themeId);
                $themeSet->setBrand($brand);

                $em->persist($themeSet);
                $em->flush();
            }
        }

        $previousEditThemeSet = $brand->getEditThemeSet();
        $brand->setEditThemeSet($themeSet);
        if ($brand->getThemeSet()->getThemeId() !== 'helpcenter'
            && $brand->getEditThemeSet()->getThemeId() === 'helpcenter'
        ) {
            $this->container->get('legacy_template_handler')->refreshLegacyTemplatesBackup();
            $this->container->get('legacy_template_handler')->copyCustomTemplates();
            $this->container->get('dp.portal.designer.theme_set_copying_service')->copyIcons($previousEditThemeSet, $themeSet);
            $themeSet->setOption('welcome_box', $previousEditThemeSet->getOption('welcome_box'));
            $variables = $previousEditThemeSet->getOption('custom_vars');
            $themeSet->setOption('custom_vars', $variables);
            $this->getPortalStylesCompiler()->recompile($variables, $themeSet);
            $this->container->getEm()->remove($previousEditThemeSet);
        }

        $em->flush();

        return new View($themeSet);
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/commit")
     * @Method({"GET"})
     */
    public function commitEditThemeSetAction()
    {
        $brand = $this->container->get('brand_stack')->getActive()->getBrand();
        if ($brand->getThemeSet()->getThemeId() !== 'helpcenter'
            && $brand->getEditThemeSet()->getThemeId() === 'helpcenter'
        ) {
            $this->container->get('legacy_template_handler')->refreshLegacyTemplatesBackup();
            $this->container->get('legacy_template_handler')->copyCustomTemplates();
        }

        $this->getStylesManager()->commitEditThemeSet();

        return new JsonResponse();
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/discard")
     * @Method({"GET"})
     */
    public function discardEditThemeSetAction()
    {
        $this->getStylesManager()->discardEditThemeSet();

        return new JsonResponse(['id' => $this->getTheme()->getId()]);
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/clone")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function cloneThemeAction(Request $request)
    {
        $newTitle = $request->request->get('title');
        if (!$newTitle) {
            throw new BadRequestHttpException('Empty title');
        }

        $newThemeSet = new ThemeSet();
        $this->container->get('dp.portal.designer.theme_set_copying_service')->copy($this->getEditThemeSet(), $newThemeSet);
        $newThemeSet->setTitle($newTitle);
        $newThemeSet->setBrand($this->container->get('brand_stack')->getActive()->getBrand());

        $this->getManager()->persist($newThemeSet);
        $this->getManager()->flush();

        return new View($newThemeSet);
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/import")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function importAction(Request $request)
    {
        return new View($this->importThemeSet($request));
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/import-and-replace")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function importAndReplaceAction(Request $request)
    {
        $themeSet = $this->importThemeSet($request, $this->getEditThemeSet());

        return new View($themeSet);
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/export")
     * @Method({"GET"})
     *
     * @return Response
     */
    public function exportAction()
    {
        $themeSet = $this->getEditThemeSet();

        $importService = $this->container->get('dp.portal.designer.theme_set_import');
        $generatedFile = $importService->exportThemeSet($themeSet);

        $response = new Response();
        $response->setContent(file_get_contents($generatedFile));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-disposition', 'attachment;filename="deskpro-theme-set-'.$themeSet->getId().'-export.zip"');
        $response->headers->set('Content-Length', filesize($generatedFile));

        $importService->cleanTmpFiles();

        return $response;
    }

    /**
     * @param Request  $request
     * @param ThemeSet $overwriteTheme
     *
     * @throws BadRequestHttpException
     *
     * @return ThemeSet
     */
    private function importThemeSet(Request $request, ThemeSet $overwriteTheme = null)
    {
        $form = $this->createForm(ZipType::class);
        $form->submit($request->files);
        if (!$form->isValid()) {
            throw new BadRequestHttpException('Unable to upload file');
        }

        /** @var UploadedFile $uploadedFile */
        $uploadedFile  = $request->files->get('file');
        $importService = $this->container->get('dp.portal.designer.theme_set_import');

        $themeSet = $importService->importThemeSet($uploadedFile->getRealPath(), $overwriteTheme);
        $importService->cleanTmpFiles();

        $fs = new Filesystem();
        $fs->remove($uploadedFile->getRealPath());

        return $themeSet;
    }
}
