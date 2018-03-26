<?php

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
