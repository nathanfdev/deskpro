<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\DevBundle\Language;

use Symfony\Component\Finder\Finder;

class PhraseProject
{
    /**
     * @var \SplFileInfo[]
     */
    private $files;

    /**
     * @param string $langDir
     * @param string $projectId
     *
     * @return PhraseProject
     */
    public static function createProject($langDir, $projectId)
    {
        switch ($projectId) {
            case 'portal':
                $files = Finder::create()
                    ->in([
                        $langDir.'/portal',
                        $langDir.'/user',
                    ])
                    ->files()
                    ->name('*.php');
                break;

            case 'agent':
                $files = Finder::create()
                    ->in([
                        $langDir.'/agent',
                    ])
                    ->files()
                    ->name('*.php');
                break;

            case 'other':
                $files = Finder::create()
                    ->in([
                        $langDir.'/adm',
                        $langDir.'/admin',
                        $langDir.'/api',
                    ])
                    ->files()
                    ->name('*.php');
                break;

            default:
                throw new \InvalidArgumentException();
        }

        return new self(iterator_to_array($files));
    }

    /**
     * PhraseProject constructor.
     *
     * @param \SplFileInfo[] $files
     */
    public function __construct(array $files)
    {
        $this->files = $files;
    }

    /**
     * @return \SplFileInfo[]
     */
    public function getFiles()
    {
        return $this->files;
    }
}
