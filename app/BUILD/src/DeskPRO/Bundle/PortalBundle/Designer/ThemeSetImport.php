<?php

namespace DeskPRO\Bundle\PortalBundle\Designer;

use Application\DeskPRO\App;
use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Template;
use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Component\Filesystem\SafeFile;
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

        register_shutdown_function([$this, 'cleanTmpFiles']);
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

        try {
            /** @var \DeskPRO\Bundle\AppBundle\Zippy\Zippy $zippy */
            $zippy = App::get('deskpro.zippy');

            // Assert that zip size is within 50Mb limit
            $zippy->openWithSizeAssertion($archivePath, 50)->extract($this->tmpDir);
        } catch (\Exception $e) {
            throw new \RuntimeException('Unable to extract theme set archive', 0, $e);
        }

        $infoPath = $this->tmpDir.DIRECTORY_SEPARATOR.'theme.json';
        if (!file_exists($infoPath)) {
            throw new \RuntimeException('Unable to import theme set from this archive');
        }

        $jsonData = json_decode(SafeFile::file_get_contents($infoPath, $this->tmpDir), true);
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

                SafeFile::assertValid($assetPath, $this->tmpDir);

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
                $sourcePath = $this->tmpDir.DIRECTORY_SEPARATOR.$templateInfo['source_path'];

                SafeFile::assertValid($sourcePath, $this->tmpDir);

                if (!file_exists($sourcePath)) {
                    continue;
                }

                $templateCode     = SafeFile::file_get_contents($sourcePath, $this->tmpDir);
                $templateCompiled = App::get('twig')->compileSource($templateCode);

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

            SafeFile::assertValid($assetPath, $assetsDir);

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

        $archivePath = $this->tmpDir.DIRECTORY_SEPARATOR.'theme-set-'.$themeSet->getId().'.zip';

        /** @var \DeskPRO\Bundle\AppBundle\Zippy\Zippy $zippy */
        $zippy = App::get('deskpro.zippy');

        $zippy->createFromDir($archivePath, $this->tmpDir);

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
