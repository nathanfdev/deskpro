<?php

namespace DeskPRO\Bundle\PortalBundle\Designer;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Template;
use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use Doctrine\ORM\EntityManager;
use Orb\Util\Strings;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ThemeSetImport.
 */
class ThemeSetImport
{
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
     * @var string
     */
    private $tmpDir;

    /**
     * Constructor.
     *
     * @param EntityManager      $em
     * @param AppEnvInterface    $appEnv
     * @param DeskproBlobStorage $blobStorage
     * @param BrandStack         $brandStack
     */
    public function __construct(
        EntityManager $em,
        AppEnvInterface $appEnv,
        DeskproBlobStorage $blobStorage,
        BrandStack $brandStack
    ) {
        $this->em          = $em;
        $this->appEnv      = $appEnv;
        $this->blobStorage = $blobStorage;
        $this->brandStack  = $brandStack;
    }

    /**
     * @param string   $archivePath
     * @param ThemeSet $overwriteTheme
     *
     * @throws \RuntimeException
     *
     * @return ThemeSet
     */
    public function importThemeSet($archivePath, ThemeSet $overwriteTheme = null)
    {
        $this->generateTmpDir();

        require_once DP_ROOT.'/vendor-src/pclzip/pclzip.lib.php';

        $archive = new \PclZip($archivePath);
        if ($archive->extract(PCLZIP_OPT_PATH, $this->tmpDir) == 0) {
            throw new \RuntimeException('Unable to extract theme set archive');
        }

        $infoPath = $this->tmpDir.DIRECTORY_SEPARATOR.'theme.json';
        if (!file_exists($infoPath)) {
            throw new \RuntimeException('Unable to import theme set from this archive');
        }

        $jsonData = json_decode(file_get_contents($infoPath), true);
        if (!isset($jsonData['theme_id'])) {
            throw new \RuntimeException('Unable to get theme_id');
        }

        // paste base info
        if ($overwriteTheme) {
            $themeSet = $overwriteTheme;
        } else {
            $themeSet = new ThemeSet();
            $themeSet->setBrand($this->brandStack->getActive()->getBrand());

            if (isset($jsonData['title']) && $jsonData['title']) {
                $themeSet->setTitle($jsonData['title']);
            } else {
                $themeSet->setTitle("Imported theme ({$jsonData['theme_id']})");
            }
        }

        $themeSet->setThemeId($jsonData['theme_id']);

        if (isset($jsonData['options'])) {
            $themeSet->setOptions($jsonData['options']);
        }

        $oldToNewBlobRefs = [];

        // paste assets
        if (isset($jsonData['assets'])) {
            foreach ($jsonData['assets'] as $assetInfo) {
                $assetPath = $this->tmpDir.DIRECTORY_SEPARATOR.$assetInfo['path'];
                if (!file_exists($assetPath)) {
                    continue;
                }

                $newBlob = $this->blobStorage->createBlobRecordFromFile(
                    $assetPath,
                    $assetInfo['filename'],
                    $assetInfo['content_type']
                );

                $asset = new ThemeSetAsset();
                $asset->setName($assetInfo['name']);
                $asset->setTags($assetInfo['tags']);
                $asset->setBlob($newBlob);

                $themeSet->addAsset($asset);

                $oldToNewBlobRefs[$assetInfo['authcode']] = $newBlob->getAuthcode();
            }
        }

        // paste templates
        if (isset($jsonData['templates'])) {
            foreach ($jsonData['templates'] as $templateInfo) {
                $sourcePath   = $this->tmpDir.DIRECTORY_SEPARATOR.$templateInfo['source_path'];
                $compiledPath = $this->tmpDir.DIRECTORY_SEPARATOR.$templateInfo['compiled_path'];

                if (!file_exists($sourcePath) || !file_exists($compiledPath)) {
                    continue;
                }

                $templateCode     = file_get_contents($sourcePath);
                $templateCompiled = file_get_contents($compiledPath);

                // replace custom blob auth codes
                foreach ($oldToNewBlobRefs as $oldAuthcode => $newAuthcode) {
                    $templateCode     = str_replace("'blob_auth_id': '$oldAuthcode'", "'blob_auth_id': '$newAuthcode'", $templateCode);
                    $templateCompiled = str_replace("'blob_auth_id': '$oldAuthcode'", "'blob_auth_id': '$newAuthcode'", $templateCompiled);
                    $templateCompiled = str_replace("\"blob_auth_id\" => \"$oldAuthcode\"", "\"blob_auth_id\" => \"$newAuthcode\"", $templateCompiled);
                }

                $template = new Template();
                $template->setName($templateInfo['name']);
                $template->setTemplate($templateCode, $templateCompiled);

                $themeSet->addTemplate($template);
            }
        }

        $this->em->persist($themeSet);
        $this->em->flush();

        return $themeSet;
    }

    /**
     * @param ThemeSet $themeSet
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function exportThemeSet(ThemeSet $themeSet)
    {
        $this->generateTmpDir();

        // copy base info
        $jsonData = [
            'theme_id'  => $themeSet->getThemeId(),
            'title'     => $themeSet->getTitle(),
            'options'   => $themeSet->getOptions(),
            'assets'    => [],
            'templates' => [],
        ];

        // copy assets
        $assetsDir = $this->tmpDir.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR;
        foreach ($themeSet->getAssets() as $asset) {
            $blob = $asset->getBlob();

            $assetPath = $assetsDir.$asset->getId().'-'.$blob->getFilename();
            $this->blobStorage->copyBlobRecordToFile($assetPath, $blob);

            $jsonData['assets'][] = [
                'name'         => $asset->getName(),
                'tags'         => $asset->getTags(),
                'path'         => $this->makeRelativePath($assetPath),
                'filename'     => $blob->getFilename(),
                'content_type' => $blob->getContentType(),
                'authcode'     => $blob->getAuthcode(),
            ];
        }

        // copy templates
        $templatesDir = $this->tmpDir.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR;
        foreach ($themeSet->getTemplates() as $template) {
            $baseName = str_replace('/', '', $template->getName());
            $baseName = str_replace(':', '', $baseName);

            $sourcePath   = $templatesDir.$template->getId().'-'.$baseName;
            $compiledPath = $templatesDir.$template->getId().'-compiled-'.$baseName;

            if (false === file_put_contents($sourcePath, $template->getTemplateCode())) {
                throw new \RuntimeException('Unable to write a template '.$sourcePath);
            }
            if (false === file_put_contents($compiledPath, $template->getTemplateCompiled())) {
                throw new \RuntimeException('Unable to write a template '.$compiledPath);
            }

            $jsonData['templates'][] = [
                'name'          => $template->getName(),
                'source_path'   => $this->makeRelativePath($sourcePath),
                'compiled_path' => $this->makeRelativePath($compiledPath),
            ];
        }

        $this->createTmpFile('theme.json', json_encode($jsonData));

        // write to archive
        require_once DP_ROOT.'/vendor-src/pclzip/pclzip.lib.php';

        $archivePath = $this->tmpDir.DIRECTORY_SEPARATOR.'theme-set-'.$themeSet->getId().'.zip';
        $archive     = new \PclZip($archivePath);

        $list = $archive->add(
            $this->tmpDir,
            \PCLZIP_OPT_REMOVE_PATH,
            $this->tmpDir
        );

        if ($list == 0) {
            throw new \RuntimeException('Unable to create an archive file of this theme set');
        }

        return $archivePath;
    }

    /**
     *
     */
    public function cleanTmpFiles()
    {
        $fs = new Filesystem();
        $fs->remove($this->tmpDir);
    }

    /**
     * @throws \RuntimeException
     */
    private function generateTmpDir()
    {
        $this->tmpDir = $this->appEnv->getUserTmpDir().DIRECTORY_SEPARATOR.uniqid('dpd', true);
        if (!mkdir($this->tmpDir, 0777, true)) {
            throw new \RuntimeException('Unable to make a tmp dir');
        }
        if (!mkdir($this->tmpDir.DIRECTORY_SEPARATOR.'assets', 0777, true)) {
            throw new \RuntimeException('Unable to make a tmp dir');
        }
        if (!mkdir($this->tmpDir.DIRECTORY_SEPARATOR.'templates', 0777, true)) {
            throw new \RuntimeException('Unable to make a tmp dir');
        }
    }

    /**
     * @param $fileName
     * @param $content
     */
    private function createTmpFile($fileName, $content)
    {
        $content = trim($content)."\n";
        $content = Strings::standardEol($content, "\r\n");

        @file_put_contents($this->tmpDir.DIRECTORY_SEPARATOR.$fileName, $content);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function makeRelativePath($path)
    {
        $path = substr($path, strlen($this->tmpDir));
        $path = ltrim($path, '/');

        return $path;
    }
}
