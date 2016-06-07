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

class Build1465207103 extends AbstractBuild
{
    public function run()
    {
        $this->out('Upgrading feedback table and status categories table for it.');
        $connection = $this->getDbConnection('default');
        $sql        = <<<SQL
        SELECT * FROM `feedback_status_categories` AS `fsc`
        WHERE `fsc`.`status_type` = 'active'
        ORDER BY `fsc`.`display_order` ASC
SQL;

        $categories = $connection->fetchAll($sql);
        $do         = 10;
        foreach ($categories as $category) {
            $do += 10;
            $connection->executeUpdate(
                'UPDATE `feedback_status_categories` SET `display_order` = :do WHERE `id` = :id',
                ['do' => $do, 'id' => $category['id']]
            );
        }
        $connection->insert(
            'feedback_status_categories',
            [
                'status_type'   => 'active',
                'title'         => 'New',
                'display_order' => 10,
            ]
        );
        $newStatusCategoryId = $connection->lastInsertId();
        $this->out('Status category \'New\' is inserted with id = '.$newStatusCategoryId);
        $this->out('Moving feedback with old style \'new\' status into just created status category.');

        $feedbackWithNewStatus = $connection->fetchAllCol(
            'SELECT `f`.`id` FROM `feedback` AS `f` WHERE `f`.`status` = \'new\''
        );

        if ($feedbackWithNewStatus) {
            $this->out(
                sprintf(
                    'We were able to found %d feedback with \'new\' status. We are going to update them.',
                    count($feedbackWithNewStatus)
                )
            );
            $connection->updateIn(
                'feedback',
                [
                    'status'             => 'active',
                    'status_category_id' => $newStatusCategoryId,
                ],
                $feedbackWithNewStatus
            );
        } else {
            $this->out('Seems that you haven\'t feedback with old \'new\' status.');
        }
    }
}
