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
