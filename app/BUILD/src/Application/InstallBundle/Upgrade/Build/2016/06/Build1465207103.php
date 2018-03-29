<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1465207103 extends AbstractBuild
{
    public function run()
    {
        $this->out('Upgrading feedback table and status categories table for it.');
        $connection = $this->getDbConnection('default');
        $sql        = <<<'SQL'
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
