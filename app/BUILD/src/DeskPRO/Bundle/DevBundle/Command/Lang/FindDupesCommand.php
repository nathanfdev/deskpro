<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\DevBundle\Command\Lang;

use DeskPRO\Component\Util\ListUtils;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class FindDupesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:lang:find-dupes')
            ->setDescription('Goes through lang files in the specified directories and detects which phrase IDs are duplicates. This can be used in the CopyDupesCommand to copy phrase text in other langs.')
            ->addArgument('dirs', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Directories to scan for lang files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dirs = $input->getArgument('dirs');
        $dirs = array_map(function ($dir) {
            if ($realDir = realpath($dir)) {
                return $realDir;
            }

            if ($realDir = realpath(getcwd().DIRECTORY_SEPARATOR.ltrim($dir, '/\\'))) {
                return $realDir;
            }

            throw new \InvalidArgumentException('Invalid path: '.$dir);
        }, $dirs);

        /** @var \SplFileInfo[] $files */
        $files = Finder::create()->in($dirs)->files()->name('*.php');

        // An array of [hash => 'ids' => [], 'phrases' => []]
        // where 'hash' is a lookup name based on the text of the phrase
        $phraseCollection = [];

        foreach ($files as $f) {
            $phrases = require $f->getPathname();
            foreach ($phrases as $id => $phrase) {
                $hash = $this->getPhraseHash($phrase);

                if (!isset($phraseCollection[$hash])) {
                    $phraseCollection[$hash] = [
                        'ids'     => [],
                        'phrases' => [],
                    ];
                }

                $phraseCollection[$hash]['ids'][]        = $id;
                $phraseCollection[$hash]['phrases'][$id] = $phrase;
            }
        }

        $dupePhrases = ListUtils::filter($phraseCollection, function ($info) {
            $ids = ListUtils::unique($info['ids']);

            return count($ids) >= 2;
        });

        $grouped = ListUtils::map($dupePhrases, function ($info) {
            return ListUtils::unique($info['ids']);
        });

        echo json_encode($grouped, \JSON_PRETTY_PRINT);
    }

    /**
     * @param string $phrase
     *
     * @return string
     */
    private function getPhraseHash($phrase)
    {
        $phrase = strtolower($phrase);
        $phrase = trim(strtolower($phrase));

        return $phrase;
    }
}
