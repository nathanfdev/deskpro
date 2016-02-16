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
namespace DeskPRO\Bundle\AppBundle\DataFixtures;

use Application\DeskPRO\DBAL\Connection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Faker\Factory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class DeskProAbstractFixture extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    const FIXTURE_ORDER                    = 0;
    const TABLE_CUSTOM_DATA_FEEDBACK       = 'custom_data_feedback';
    const TABLE_CUSTOM_DEF_FEEDBACK        = 'custom_def_feedback';
    const TABLE_FEEDBACK                   = 'feedback';
    const TABLE_FEEDBACK_CATEGORIES        = 'feedback_categories';
    const TABLE_FEEDBACK_STATUS_CATEGORIES = 'feedback_status_categories';
    const TABLE_LABELS_FEEDBACK            = 'labels_feedback';
    const TABLE_LANGUAGES                  = 'languages';
    const TABLE_PEOPLE                     = 'people';
    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Connection
     */
    protected $db;

    /**
     * DpFixture constructor.
     */
    public function __construct()
    {
        $this->faker = Factory::create();
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->db        = $this->container->get('database_connection');
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return self::FIXTURE_ORDER;
    }

    protected function randomArrayValue(array $array)
    {
        return $array[array_rand($array)];
    }

    /**
     * @param string       $table Table name for fetching
     * @param string|array $fields
     * @param array        $where
     *
     * @return array
     */
    protected function fetchRelatedEntitiesIds($fields, $table, $where = [])
    {
        $sql = 'SELECT ';
        if (is_array($fields)) {
            $sql .= implode(', ', $fields);
        } elseif (is_string($fields)) {
            $sql .= $fields;
        }
        $sql .= ' FROM '.$table;
        if (!empty($where)) {
            $sql .= ' WHERE ';
            $statements = [];
            foreach ($where as $statement) {
                $comp         = isset($statement['comp']) ? $statement['comp'] : '=';
                $statements[] = $statement['field'].' '.$comp.' "'.$statement['value'].'"';
            }
            $sql .= implode(', ', $statements);
        }
        $ids = $this->db->fetchAllCol($sql);

        if (!$ids) {
            throw new \RuntimeException('Please import '.$table.' first');
        }

        return $ids;
    }
}
