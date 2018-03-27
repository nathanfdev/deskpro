<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1478687110 extends AbstractBuild
{
    public function run()
    {
        $this->out('Updating Feedbacks');
        $sql     = 'select f.id, f.content from feedback f join people p on p.id = f.person_id and p.is_agent = 0 ';
        $results = $this->getDbConnection()->fetchAll($sql);
        foreach ($results as $row) {
            $content = htmlentities(nl2br($row['content']));
            $this->getDbConnection()->executeQuery('update feedback set content = :content where id = :id', [
                'id'      => $row['id'],
                'content' => $content,
            ]);
        }
    }
}
