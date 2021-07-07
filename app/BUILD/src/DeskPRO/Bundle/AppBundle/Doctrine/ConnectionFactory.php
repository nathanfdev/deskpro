<?php

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
            $mock = \Mockery::mock(PDOStub::class);
            $mock->shouldReceive('setAttribute')->andReturn();
            $mock->shouldReceive('getAttribute')->andReturn();
            $mock->shouldReceive('beginTransaction')->andReturn();
            $mock->shouldReceive('commit')->andReturn();
            $mock->shouldReceive('rollback')->andReturn();
            $mock->shouldReceive('rollback')->andReturn();
            $mock->shouldReceive('quote')->andReturnUsing(function() { return 'xxx'; });

            $makeStatement = function () {
                $s = \Mockery::mock('Doctrine\DBAL\Driver\PDOStatement');
                $s->shouldDeferMissing();
                $s->shouldReceive('setFetchMode');
                $s->shouldReceive('bindValue');
                $s->shouldReceive('fetchAll')->andReturnUsing(function () { return []; });
                $s->shouldReceive('fetchObject')->andReturnUsing(function () { return false; });
                $s->shouldReceive('fetchColumn')->andReturnUsing(function () { return false; });
                $s->shouldReceive('fetch')->andReturnUsing(function () { return false; });
                $s->shouldReceive('rowCount')->andReturnUsing(function () { return 0; });

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
