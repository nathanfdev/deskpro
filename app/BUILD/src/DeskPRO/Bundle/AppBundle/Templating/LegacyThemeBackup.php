<?php

namespace DeskPRO\Bundle\AppBundle\Templating;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Template;
use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use Doctrine\ORM\EntityManager;

/**
 * Class LegacyThemeBackup.
 */
class LegacyThemeBackup
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
     * Constructor.
     *
     * @param EntityManager      $em
     * @param AppEnvInterface    $appEnv
     * @param DeskproBlobStorage $blobStorage
     */
    public function __construct(EntityManager $em, AppEnvInterface $appEnv, DeskproBlobStorage $blobStorage)
    {
        $this->em          = $em;
        $this->appEnv      = $appEnv;
        $this->blobStorage = $blobStorage;
    }

    /**
     * @return Blob|null
     */
    public function refreshLegacyTemplatesBackup()
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

        /** @var Template[] $templates */
        $templates = $qb->getQuery()->getResult();
        if (!$templates) {
            return;
        }

        $tmpDir = $this->appEnv->getUserTmpDir().DIRECTORY_SEPARATOR.uniqid('dpd', true);
        if (!mkdir($tmpDir, 0777, true)) {
            throw new \RuntimeException('Unable to make a tmp dir');
        }

        $templatesDir = $tmpDir.DIRECTORY_SEPARATOR.'deskpro-templates';
        if (!is_dir($templatesDir) && !mkdir($templatesDir, 0777, true)) {
            throw new \RuntimeException('Unable to make the templates dir');
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

            if (!file_put_contents($name, $template->getTemplateCode())) {
                throw new \RuntimeException('Unable to write template');
            }
        }

        // write to archive
        require_once DP_ROOT.'/vendor-src/pclzip/pclzip.lib.php';

        $archive = new \PclZip($archivePath);
        $list    = $archive->add($tmpDir, \PCLZIP_OPT_REMOVE_PATH, $tmpDir);

        if ($list == 0) {
            throw new \RuntimeException('Unable to create an archive file of legacy templates');
        }

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
}
