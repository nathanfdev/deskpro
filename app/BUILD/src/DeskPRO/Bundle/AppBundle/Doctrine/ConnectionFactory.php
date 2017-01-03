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

namespace DeskPRO\Bundle\AppBundle\Doctrine;

use DeskPRO\Component\Testing\PDO\PDOStub;
use Doctrine\Bundle\DoctrineBundle\ConnectionFactory as BaseConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;

class ConnectionFactory extends BaseConnectionFactory
{
    /**
     * {@inheritdoc}
     */
    public function createConnection(array $params, Configuration $config = null, EventManager $eventManager = null, array $mappingTypes = [])
    {
        // This is used by some kernel cache warmer scrpts to allow building without a real db connection
        // TODO This is a bit of a hack and sholud be corrected so the warmers dont need it

        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        if ($DP_ENV && $DP_ENV->getRuntimeVar('is_building', false)) {
            $mock = \Mockery::mock(new PDOStub());
            $mock->shouldReceive('setAttribute')->andReturn();
            $mock->shouldReceive('getAttribute')->andReturn();
            $mock->shouldReceive('beginTransaction')->andReturn();
            $mock->shouldReceive('commit')->andReturn();
            $mock->shouldReceive('rollback')->andReturn();
            $mock->shouldReceive('rollback')->andReturn();

            $makeStatement = function () {
                $s = \Mockery::mock('Doctrine\DBAL\Driver\PDOStatement');
                $s->shouldDeferMissing();
                $s->shouldReceive('setFetchMode');
                $s->shouldReceive('bindValue');

                return $s;
            };
            $mock->shouldReceive('query')->andReturnUsing($makeStatement);
            $mock->shouldReceive('prepare')->andReturnUsing($makeStatement);

            $params['pdo']         = $mock;
            $params['driverClass'] = 'Doctrine\\DBAL\\Driver\\PDOMySql\\Driver';
        }

        return parent::createConnection($params, $config, $eventManager, $mappingTypes);
    }
}
