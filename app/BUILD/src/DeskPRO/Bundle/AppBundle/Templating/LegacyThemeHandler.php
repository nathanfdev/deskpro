<?php

namespace DeskPRO\Bundle\AppBundle\Templating;

use Application\DeskPRO\App;
use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Template;
use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

/**
 * Class LegacyThemeHandler.
 */
class LegacyThemeHandler
{
    const BACKUP_SYS_NAME = 'legacy-templates-backup';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var AppEnvInterface
     */
    private $appEnv;

    /**
     * @var DeskproBlobStorage
     */
    private $blobStorage;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param AppEnvInterface $appEnv
     * @param DeskproBlobStorage $blobStorage
     * @param BrandStack $brandStack
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $em, AppEnvInterface $appEnv, DeskproBlobStorage $blobStorage, BrandStack $brandStack, LoggerInterface $logger)
    {
        $this->em          = $em;
        $this->appEnv      = $appEnv;
        $this->blobStorage = $blobStorage;
        $this->brandStack  = $brandStack;
        $this->logger      = $logger;
    }

    /**
     * @return Blob|null
     */
    public function refreshLegacyTemplatesBackup()
    {
        $tmpDir = $this->appEnv->getUserTmpDir().DIRECTORY_SEPARATOR.uniqid('dpd', true);
        if (!mkdir($tmpDir, 0777, true)) {
            throw new \RuntimeException('Unable to make a tmp dir');
        }

        $templatesDir = $tmpDir.DIRECTORY_SEPARATOR.'deskpro-templates';
        if (!is_dir($templatesDir) && !mkdir($templatesDir, 0777, true)) {
            throw new \RuntimeException('Unable to make the templates dir');
        }

        $templates = $this->getCustomTemplates();
        if (!$templates) {
            return;
        }

        $archiveName = count($templates).'-legacy-templates-backup.zip';
        $archivePath = $tmpDir.DIRECTORY_SEPARATOR.$archiveName;

        foreach ($templates as $template) {
            $baseName = str_replace('/', '', $template->getName());
            $baseName = str_replace(':', '', $baseName);

            $brandId  = $template->getThemeSet()->getBrand()->getId();
            $brandDir = $templatesDir.DIRECTORY_SEPARATOR.'brand_'.$brandId;

            if (!is_dir($brandDir) && !mkdir($brandDir, 0777, true)) {
                throw new \RuntimeException('Unable to make the brand dir');
            }

            $name = $brandDir.DIRECTORY_SEPARATOR.$baseName;

           $fileWritten = file_put_contents($name, $template->getTemplateCode());

           if($fileWritten === '0'){
               $this->logger->error(sprintf('Template %s is empty', $template->getName()));
           }

           if(false === $fileWritten) {
              throw new \RuntimeException('Unable to write template');
           }
        }

        // write to archive
        /** @var \DeskPRO\Bundle\AppBundle\Zippy\Zippy $zippy */
        $zippy = App::get('deskpro.zippy');
        $zippy->createFromDir($archivePath, $tmpDir);

        // create new blob file
        $this->em
            ->createQueryBuilder()
            ->delete()
            ->from(Blob::class, 'b')
            ->where('b.sys_name = :sys_name')
            ->setParameter('sys_name', self::BACKUP_SYS_NAME)
            ->getQuery()
            ->execute()
        ;

        $blob = $this->blobStorage->createBlobRecordFromFile($archivePath, $archiveName, 'application/zip');
        $blob->setSysName(self::BACKUP_SYS_NAME);

        $this->em->persist($blob);
        $this->em->flush();

        return $blob;
    }

    /**
     * When the legacy current theme is being replaced with the new helpcenter one most of the custom templates can't be re-used
     * because the html structure has been changed a lot, but some of them still can be copied though, e.g. custom header and footer.
     */
    public function copyCustomTemplates()
    {
        $legacyTemplates = $this->getCustomTemplates();
        if (!$legacyTemplates) {
            return;
        }

        $copyTemplateNames = [
            'Theme:Internal:custom-header.html.twig',
            'Theme:Internal:custom-footer.html.twig',
            'Theme:Internal:custom-head-include.html.twig',
        ];

        $brand        = $this->brandStack->getActive()->getBrand();
        $editThemeSet = $brand->getEditThemeSet();

        foreach ($legacyTemplates as $legacyTemplate) {
            if ($editThemeSet === $legacyTemplate->getThemeSet()) {
                continue;
            }

            if (in_array($legacyTemplate->getName(), $copyTemplateNames)) {
                $newTemplate = new Template();
                $newTemplate->setName($legacyTemplate->getName());
                $newTemplate->setTemplate($legacyTemplate->getTemplateCode(), $legacyTemplate->getTemplateCompiled());
                $newTemplate->setThemeSet($editThemeSet);

                $this->em->persist($newTemplate);
            }
        }

        $this->em->flush();
    }

    /**
     * @return Template[]
     */
    private function getCustomTemplates()
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('t, ts')
            ->from(Template::class, 't')
            ->join('t.theme_set', 'ts')
            ->where('ts.theme_id IN (:theme_ids)')
            ->setParameter('theme_ids', ['standard', 'sidebar'])
            ->groupBy('t.name')
        ;

        return $qb->getQuery()->getResult();
    }
}
