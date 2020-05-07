<?php

namespace DeskPRO\Bundle\DevBundle\Command\Lang;

use CrowdinApiClient\Api\AbstractApi;
use CrowdinApiClient\Api\ScreenshotApi;
use CrowdinApiClient\Api\SourceStringApi;
use CrowdinApiClient\Api\StorageApi;
use CrowdinApiClient\Crowdin;
use CrowdinApiClient\Model\BaseModel;
use CrowdinApiClient\Model\Screenshot;
use CrowdinApiClient\Model\SourceString;
use CrowdinApiClient\Model\Storage;
use CrowdinApiClient\ModelCollection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class SyncScreenshotsCommand.
 *
 * To run the command you need to set settings first:
 *
 * $SETTINGS['lang.crowdin.project_id']   = 12345;
 * $SETTINGS['lang.crowdin.access_token'] = 'your_token';
 */
class SyncScreenshotsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dpdev:lang:crowdin:sync-screenshots')
            ->addArgument('path');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $crowdin = new Crowdin([
            'access_token' => $this->getAccessToken(),
        ]);

        /** @var StorageApi $storageApi */
        $storageApi = $crowdin->getApi('storage');
        /** @var ScreenshotApi $screenshotApi */
        $screenshotApi = $crowdin->getApi('screenshot');
        /** @var SourceStringApi $stringsApi */
        $stringsApi = $crowdin->getApi('sourceString');

        $screenshotIds     = [];
        $missedPhrases     = [];
        $missedScreenshots = [];

        $output->writeln('Load data');

        // check the input dir doesn't have duplicates
        $filenames = [];
        foreach ($this->getFinder($input) as $file) {
            $filename = $file->getFilename();
            if (isset($filenames[$filename])) {
                $output->writeln("Unable to sync, a duplicate file has been found $filename");

                return 1;
            }

            $filenames[$filename] = 1;
        }

        // load existing data
        try {
            /** @var Screenshot[] $screenshots */
            $screenshots = $this->loadCollection($screenshotApi);
            /** @var SourceString[] $strings */
            $strings = $this->loadCollection($stringsApi);
        } catch (\Exception $e) {
            $output->writeln('Unable to load existing screenshots');

            return 1;
        }

        // insert and update screenshots
        $output->writeln('Sync screenshots');

        foreach ($this->getFinder($input) as $file) {
            $newName    = crc32($file->getContents()).'__'.$file->getFilename();
            $phraseId   = $file->getBasename('.'.$file->getExtension());
            $screenshot = $this->findScreenshotByFilename($screenshots, $newName);

            if ($screenshot) {
                if ($screenshot->getName() === $newName) {
                    $output->writeln("$newName (up to date)");
                } else {
                    $output->writeln("$newName (replace)");

                    try {
                        /** @var Storage $fileStorage */
                        $fileStorage = $storageApi->create($file->openFile());
                        $screenshotApi->replace($this->getProjectId(), $screenshot->getId(), $fileStorage->getId(), $newName);
                    } catch (\Exception $e) {
                        $output->writeln("Unable to replace $newName");
                    }
                }
            } else {
                $output->writeln("$newName (insert)");

                try {
                    /** @var Storage $fileStorage */
                    $fileStorage = $storageApi->create($file->openFile());
                    $screenshot  = $screenshotApi->create($this->getProjectId(), [
                        'storageId' => $fileStorage->getId(),
                        'name'      => $newName,
                    ]);
                } catch (\Exception $e) {
                    $output->writeln("Unable to insert $newName");
                }
            }

            if ($screenshot) {
                $string = $this->findString($strings, $phraseId);
                if ($string) {
                    if (!$this->screenshotHasString($screenshot, $string)) {
                        $output->writeln("Tag screenshot $phraseId");

                        try {
                            $screenshotApi->addTag($this->getProjectId(), $screenshot->getId(), [[
                                'stringId' => $string->getId(),
                            ]]);
                        } catch (\Exception $e) {
                            $output->writeln("Unable to tag screenshot $newName");
                        }
                    }
                } else {
                    $missedPhrases[] = $phraseId;
                }

                $screenshotIds[] = $screenshot->getId();
            }
        }

        // remove outdated ones
        foreach ($screenshots as $screenshot) {
            if (!in_array($screenshot->getId(), $screenshotIds)) {
                $output->writeln($screenshot->getName().' (delete)');

                try {
                    $screenshotApi->delete($this->getProjectId(), $screenshot->getId());
                } catch (\Exception $e) {
                    $output->writeln('Unable to delete '.$screenshot->getName());
                }
            }
        }

        // check for the missing screenshots
        // refresh screenshots
        try {
            /** @var Screenshot[] $screenshots */
            $screenshots = $this->loadCollection($screenshotApi);
            foreach ($strings as $string) {
                if (!$this->findScreenshotByString($screenshots, $string)) {
                    $missedScreenshots[] = $string->getContext();
                }
            }
        } catch (\Exception $e) {
            $output->writeln('Unable to load existing screenshots');

            return 1;
        }

        // display sync warnings
        if ($missedPhrases) {
            $output->writeln('No matched phrase has been found for the screenshots:');
            foreach ($missedPhrases as $phrase) {
                $output->writeln($phrase);
            }
        }
        if ($missedScreenshots) {
            $output->writeln('No matched screenshot has been found for the phrases:');
            foreach ($missedScreenshots as $phrase) {
                $output->writeln($phrase);
            }
        }

        return 0;
    }

    /**
     * @param InputInterface $input
     *
     * @return Finder
     */
    private function getFinder(InputInterface $input)
    {
        $finder = new Finder();
        $finder
            ->files()
            ->in($input->getArgument('path'))
            ->notName('*.html')
        ;

        return $finder;
    }

    /**
     * @param AbstractApi $api
     *
     * @return BaseModel[]
     */
    private function loadCollection(AbstractApi $api)
    {
        $items  = [];
        $limit  = 500;
        $offset = 0;

        do {
            /** @var BaseModel[]|ModelCollection $response */
            $response = $api->list($this->getProjectId(), [
                'limit'  => $limit,
                'offset' => $offset,
            ]);

            foreach ($response as $item) {
                $items[] = $item;
            }

            $offset += $limit;
        } while ($response->count() > 0);

        return $items;
    }

    /**
     * @param Screenshot[] $screenshots
     * @param string       $filename
     *
     * @return Screenshot|null
     */
    private function findScreenshotByFilename(array $screenshots, $filename)
    {
        foreach ($screenshots as $screenshot) {
            if ($this->matchFilename($screenshot, $filename)) {
                return $screenshot;
            }
        }

        return;
    }

    /**
     * @param Screenshot[] $screenshots
     * @param SourceString $string
     *
     * @return Screenshot|null
     */
    private function findScreenshotByString(array $screenshots, SourceString $string)
    {
        foreach ($screenshots as $screenshot) {
            if ($this->screenshotHasString($screenshot, $string)) {
                return $screenshot;
            }
        }

        return;
    }

    /**
     * @param SourceString[] $strings
     * @param string         $phraseId
     *
     * @return SourceString
     */
    private function findString(array $strings, $phraseId)
    {
        foreach ($strings as $string) {
            $context = preg_replace('#\s->\s(.+)#', '\\1', $string->getContext());

            if ($context === $phraseId) {
                return $string;
            }
        }

        return;
    }

    /**
     * @param Screenshot   $screenshot
     * @param SourceString $string
     *
     * @return bool
     */
    private function screenshotHasString(Screenshot $screenshot, SourceString $string)
    {
        foreach ($screenshot->getTags() as $tag) {
            if ($tag['stringId'] === $string->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Screenshot $screenshot
     * @param string     $filename
     *
     * @return bool
     */
    private function matchFilename(Screenshot $screenshot, $filename)
    {
        $pattern = '#^\d+__(.+)$#';
        $oldName = preg_replace($pattern, '\\1', $screenshot->getName());
        $newName = preg_replace($pattern, '\\1', $filename);

        return $oldName === $newName;
    }

    /**
     * @return int
     */
    private function getProjectId()
    {
        return $this->getContainer()->get('settings_resolver')->getGlobalSettings()->get('lang.crowdin.project_id');
    }

    /**
     * @return string
     */
    private function getAccessToken()
    {
        return $this->getContainer()->get('settings_resolver')->getGlobalSettings()->get('lang.crowdin.access_token');
    }
}
