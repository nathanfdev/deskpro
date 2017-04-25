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

class Build1467973149 extends AbstractBuild
{
    public function run()
    {
        $this->out('Fix broken snippet rows missing ref_type/ref_id');

        $db   = $this->getDbConnection('default');
        $recs = $db->fetchAll("
            SELECT id, ref
            FROM object_lang
            WHERE
              (ref LIKE 'text_snippets.%' OR ref LIKE 'text_snippet_categories.%')
              AND (ref_type IS NULL OR ref_id IS NULL)
        ");

        foreach ($recs as $rec) {
            list($refType, $refId) = explode('.', $rec['ref'], 2);
            $db->update('object_lang', [
                'ref_type' => $refType,
                'ref_id'   => $refId,
            ], ['id' => $rec['id']]);
        }
    }
}
