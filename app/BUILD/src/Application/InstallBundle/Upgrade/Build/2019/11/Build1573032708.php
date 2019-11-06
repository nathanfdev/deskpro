<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1573032708 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $oldLanguage = $this->getDbConnection()->fetchAll("SELECT id FROM `languages` WHERE sys_name = 'portuguese'");
        if (!$oldLanguage) {
            return;
        }

        $newLanguage = $this->getDbConnection()->fetchAll("SELECT id FROM `languages` WHERE sys_name ='portuguese_pt'");
        if ($newLanguage) {
            // we need to update relations and remove them
            $oldLangId = $oldLanguage[0]['id'];
            $newLangId = $newLanguage[0]['id'];

            $tables = [
                'people',
                'phrases',
                'news',
                'articles',
                'downloads',
                'topics',
                'community_topics',
                'object_lang',
                'tickets',
                'tickets_search_active',
                'snippet_changelog',
                'snippet_use_log',
                'snippet_translations',
            ];

            // fix all recent content to use the same language, set the old lang
            foreach ($tables as $table) {
                $this->execDbQuery('default', "UPDATE IGNORE `$table` SET language_id = $oldLangId WHERE language_id = $newLangId");
            }

            $this->execDbQuery('default', "DELETE FROM `languages` WHERE sys_name = 'portuguese_pt'");
        }

        // replace the old language with the new format
        $this->execDbQuery('default', "UPDATE `languages` SET sys_name = 'portuguese_pt', title = 'Português (Europeu)', base_filepath = '%DP_ROOT%/locales/pt-PT', locale = 'pt-PT', plural_categories = 'one,other', plural_formula = 'n != 1', flag_image = 'locale_pt-PT.png' WHERE sys_name = 'portuguese'");
    }
}
