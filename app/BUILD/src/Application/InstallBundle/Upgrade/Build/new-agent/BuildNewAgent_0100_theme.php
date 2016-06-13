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

namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0100_theme extends AbstractBuild
{
    public function run()
    {
        $db = $this->getDbConnection('default');

        #----------------------------------------
        # rename common tpls
        #----------------------------------------

        $renames = [
            'UserBundle::custom-headinclude.html.twig' => 'Theme:Internal:custom-head-include.html.twig',
            'UserBundle::custom-header.html.twig'      => 'Theme:Internal:custom-header.html.twig',
            'UserBundle::custom-footer.html.twig'      => 'Theme:Internal:custom-footer.html.twig',
        ];

        foreach ($renames as $oldName => $newName) {
            $db->update('templates', ['name' => $newName], ['name' => $oldName]);
        }

        #----------------------------------------
        # backup/remove all others
        #----------------------------------------

        $backupTemplates = $db->fetchAll("
            SELECT id, name, template_code
            FROM templates
            WHERE name LIKE 'UserBundle:%' OR name LIKE 'DeskPRO:CustomBlocks:%'
        ");

        if ($backupTemplates) {
            $dir = $this->getBackupDir().DIRECTORY_SEPARATOR.'tpl-backups'.DIRECTORY_SEPARATOR.date('Y-m-d');
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0777, true)) {
                    throw new \Exception('Could not create backup directory at '.$dir);
                }
            }

            foreach ($backupTemplates as $tpl) {
                @file_put_contents(
                    $dir.$tpl['id'].'--'.str_replace(':', '_', $tpl['name']),
                    $tpl['template_code']
                );
                $this->container->getDb()->delete('templates', array('id' => $tpl['id']));
            }
        }

        $this->recompileCustomTemplates();

        #----------------------------------------
        # copy logo blob
        #----------------------------------------

        $blobId  = $this->readSetting('core.deskpro_logo_blob');
        $blobRow = $db->fetchAssoc('SELECT * FROM blobs WHERE id = ?', [$blobId]);

        if ($blobRow) {
            $themeSetId = $db->fetchColumn('SELECT theme_set_id FROM brands LIMIT 1');
            $db->insert('theme_set_assets', [
                'theme_set_id' => $themeSetId,
                'blob_id'      => $blobRow['id'],
                'name'         => $blobRow['filename'],
                'tags'         => 'custom_logo',
                'date_created' => date('Y-m-d H:i:s'),
                'date_updated' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}

//[[build:1460678423]]
