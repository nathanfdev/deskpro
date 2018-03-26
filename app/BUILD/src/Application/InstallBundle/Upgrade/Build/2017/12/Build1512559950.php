<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1512559950 extends AbstractBuild
{
    public function run()
    {
        $this->out('Add Email TemplateSet');

        $this->execDbQuery('default', 'INSERT INTO theme_sets 
          (`id`, `theme_id`, `options`)
          VALUES (NULL, \'email_templates\', \'[]\')');

        // createBlobRecordFromFile
        $data      = file_get_contents(DP_ROOT.'/src/Application/AgentBundle/Resources/assets/agent-quickstart/en_US.pdf');
        $pdfBlobId = $this->saveBlob($data, 'Getting Started with DeskPRO.pdf', 'application/pdf');

        $this->container->getDb()->executeUpdate('UPDATE blobs SET sys_name = ? WHERE id = ?', ['agent-quickstart', $pdfBlobId]);
    }
}
