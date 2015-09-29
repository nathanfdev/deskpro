<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DpTestSrc\TestBundle;

/**
 * AbstractDbSet does so much work that is is basically what this class *should* be, but this is just a service we
 * use in test code to just install a db set.
 */
class DataSetManager
{
    /**
     * @var DataSet\DataSetInterface[]
     */
    private $data_sets;

    public function __construct(array $data_Sets)
    {
        $this->data_sets = $data_Sets;
    }

    public function install($db_set_id)
    {
        foreach ($this->data_sets as $dbset) {
            if ($db_set_id === $dbset->getId()) {
                $dbset->install();

                return;
            }
        }

        throw new \InvalidArgumentException('data set "'.$db_set_id.'" not found');
    }
}
