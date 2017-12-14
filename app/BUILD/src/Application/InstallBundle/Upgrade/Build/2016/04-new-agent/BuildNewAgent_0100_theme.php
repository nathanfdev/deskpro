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

class BuildNewAgent_0100_theme extends AbstractBuild
{
    public function run()
    {
        $db = $this->getDbConnection('default');

        $brands = $db->fetchAll('SELECT id, theme_set_id, edit_theme_set_id FROM brands LIMIT 1');

        //----------------------------------------
        // rename common tpls
        //----------------------------------------

        $renames = [
            'UserBundle::custom-headinclude.html.twig' => 'Theme:Internal:custom-head-include.html.twig',
            'UserBundle::custom-header.html.twig'      => 'Theme:Internal:custom-header.html.twig',
            'UserBundle::custom-footer.html.twig'      => 'Theme:Internal:custom-footer.html.twig',
        ];

        foreach ($renames as $oldName => $newName) {
            $templateCode = $db->fetchColumn('
                SELECT template_code
                FROM templates
                WHERE theme_set_id IS NULL AND name = ? LIMIT 1
            ', [$oldName]);

            if ($templateCode) {
                foreach ($brands as $brand) {
                    foreach ([$brand['theme_set_id'], $brand['edit_theme_set_id']] as $themeSetId) {
                        $this->out(sprintf('Brand %s, Theme %s, Template %s', $brand['id'], $themeSetId, $newName));
                        $db->insert('templates', [
                            'theme_set_id'      => $themeSetId,
                            'name'              => $newName,
                            'template_code'     => $templateCode,
                            'template_compiled' => '', // will be reset in the next step below
                            'date_created'      => date('Y-m-d H:i:s'),
                            'date_updated'      => date('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }
        }

        //----------------------------------------
        // backup/remove all others
        //----------------------------------------

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
                $backupPath = $dir.DIRECTORY_SEPARATOR.$tpl['id'].'--'.str_replace(':', '_', $tpl['name']);
                @file_put_contents(
                    $backupPath,
                    $tpl['template_code']
                );
                $this->out(sprintf('Backup %s to %s', $tpl['name'], $backupPath));
                $this->container->getDb()->delete('templates', ['id' => $tpl['id']]);
            }
        }

        //----------------------------------------
        // copy logo blob
        //----------------------------------------

        $blobId  = $this->readSetting('core.deskpro_logo_blob');
        $blobRow = $db->fetchAssoc('SELECT * FROM blobs WHERE id = ?', [$blobId]);

        if ($blobRow) {
            try {
                $logoDat = $this->downloadBlob($blobRow['id']);

                if ($logoDat) {
                    $ins = [];

                    $this->out(sprintf('Copying logo %s %s %sbytes', $blobRow['id'], $blobRow['filename'], $blobRow['filesize']));

                    foreach ($brands as $brand) {
                        foreach ([$brand['theme_set_id'], $brand['edit_theme_set_id']] as $themeSetId) {
                            $logoBlobId = $this->saveBlob($logoDat, $blobRow['filename'], $blobRow['content_type']);

                            $this->out(sprintf('Brand %s, Theme %s, Logo Blob %s', $brand['id'], $themeSetId, $logoBlobId));
                            $ins[] = [
                                'theme_set_id' => $themeSetId,
                                'blob_id'      => $logoBlobId,
                                'name'         => $blobRow['filename'],
                                'tags'         => 'custom_logo',
                                'date_created' => date('Y-m-d H:i:s'),
                                'date_updated' => date('Y-m-d H:i:s'),
                            ];
                        }
                    }

                    if ($ins) {
                        $db->batchInsert('theme_set_assets', $ins, true);
                    }
                }
            } catch (\Exception $e) {
            }
        }
    }
}

//[[build:1460678423]]
