<?php

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
