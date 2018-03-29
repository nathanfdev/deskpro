<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1479817816 extends AbstractBuild
{
    public function run()
    {
        $this->out('Cleanup empty labels');
        $pattern = "DELETE FROM %s WHERE label = ''";
        $tables  = [
            'labels_blobs',
            'labels_chat_conversations',
            'labels_downloads',
            'labels_feedback',
            'labels_news',
            'labels_organizations',
            'labels_people',
            'labels_tasks',
            'labels_tickets',
            'label_defs',
        ];
        foreach ($tables as $table) {
            $this->execDbQuery('default', sprintf($pattern, $table));
        }
    }
}
