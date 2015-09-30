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

namespace Application\ImportBundle\Reader\ZenDesk\Fixtures\CoreAPI;

use Application\ImportBundle\Reader\ZenDesk\Fixtures\AbstractFixture;
use Application\ImportBundle\Reader\ZenDesk\Fixtures\FixtureDeleteInterface;
use DateTime;

/**
 * ZenDesk custom fields fixtures.
 *
 * Class AbstractFields
 */
abstract class AbstractFields extends AbstractFixture implements FixtureDeleteInterface
{
    /**
     * {@inheritdoc}
     */
    protected function createItem($num, DateTime $initial_time, DateTime $end_time)
    {
        $params = $this->createParams($num);

        $this->logger->info('Request params:');
        $this->logger->info(json_encode($params));

        $response = $this->getClient()->create($params);

        $this->logger->info('Field created successfully');
        $this->logger->debug(json_encode($response));
    }

    /**
     * @param int $num
     *
     * @return array
     */
    protected function createParams($num)
    {
        $types = array('text', 'checkbox', 'date', 'integer', 'decimal', 'regexp', 'tagger');
        $type  = $types[rand(0, count($types) - 1)];

        $params = array(
            'type'        => $type,
            'title'       => 'Field '.$num,
            'description' => 'Field description '.$num,
            'required'    => $this->getRandomBool(),
        );

        if ($type === 'tagger') {
            for ($i = 0; $i < 5; ++$i) {
                $offset                           = $num + $i;
                $params['custom_field_options'][] = array(
                    'name'  => 'Option '.$offset,
                    'value' => 'value_'.$offset,
                );
            }
        }
        if ($type === 'regexp') {
            $params['regexp_for_validation'] = '.*';
        }

        return $params;
    }

    /**
     * @return mixed
     */
    abstract protected function getClient();
}
