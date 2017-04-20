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

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\DBAL\Connection;

class Build1482156819 extends AbstractBuild
{
    public function run()
    {
        $this->out('Fix templates');

        // if body-include is customised but head-include is not (or visa versa),
        // we need to customise the other. this is because we've switched the order
        // of some assets around, and if one is edited but not the other, then
        // assets wont load correctly.

        $themeSetIds = $this->getDbConnection()->fetchAllCol('SELECT id FROM theme_sets');
        foreach ($themeSetIds as $themeSetId) {
            $this->processTheme($themeSetId);
        }
    }

    /**
     * @param int $themeSetId
     */
    private function processTheme($themeSetId)
    {
        $db       = $this->getDbConnection();
        $tplNames = [
            'Theme:Internal:head-include.html.twig',
            'Theme:Internal:body-include.html.twig',
        ];

        $templateNames = $db->fetchAllCol('
            SELECT name
            FROM templates
            WHERE
              theme_set_id = ?
              AND name IN (?)
        ', [$themeSetId, $tplNames], [\PDO::PARAM_INT, Connection::PARAM_INT_ARRAY]);

        $count = count($templateNames);

        // We need either 0 or 2
        // if we only have 1, need to create the other
        if ($count && $count !== 2) {
            $have = array_pop($templateNames);

            $create = $have === 'Theme:Internal:body-include.html.twig'
                ? 'Theme:Internal:head-include.html.twig'
                : 'Theme:Internal:body-include.html.twig';

            $db->insertIgnore('templates', [
                'theme_set_id'      => $themeSetId,
                'name'              => $create,
                'template_code'     => $this->getTplSource($create),
                'template_compiled' => '', // real tpl source will get recompiled as part of PostBuild
                'date_created'      => date('Y-m-d H:i:s'),
                'date_updated'      => date('Y-m-d H:i:s'),
            ]);

            $this->out('Created legacy '.$create.' template for theme '.$themeSetId);
        }
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function getTplSource($name)
    {
        switch ($name) {
            case 'Theme:Internal:head-include.html.twig':
                return file_get_contents(__DIR__.'/res/Build1482156819/head-include.html.twig');

            case 'Theme:Internal:body-include.html.twig':
                return file_get_contents(__DIR__.'/res/Build1482156819/body-include.html.twig');

            default:
                throw new \OutOfBoundsException();
        }
    }
}
