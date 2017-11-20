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

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Application\Searcher;

use Application\DeskPRO\Searcher\PersonSearch;
use \Application\DeskPRO\DependencyInjection\DeskproContainer;
use \Application\DeskPRO\DBAL\Connection;
use DpTest\DeskProTestCase;
use Application\DeskPRO\App;
use Orb\Util\Util;

class PersonSearchTest extends DeskProTestCase
{
    public function searchByUserGroupDataProvider()
    {
        return [
            // not groups [1,2]
            [
                [1,2],
                PersonSearch::OP_NOT,
                [1,2,3],
                [4,5,6],
                [
                    'joins' => [],
                    'wheres' => [
                        "((people.id NOT IN ('1','2','3') OR people.id IS NULL) AND (people.organization_id NOT IN ('4','5','6') OR people.organization_id IS NULL))",
                        "people.is_deleted = 0"
                    ],
                    'wheres_any' => []
                ]
            ],
            // not groups [1,2] and a lot of users
            [
                [1,2],
                PersonSearch::OP_NOT,
                array_fill(0, 1001, mt_rand(1, 1000)),
                [4,5,6],
                [
                    'joins' => [
                        [
                            'person2usergroups',
                            'LEFT JOIN person2usergroups AS j_1 ON (j_1.person_id = people.id)'
                        ]
                    ],
                    'wheres' => [
                        "((j_1.usergroup_id NOT IN ('1','2') OR j_1.usergroup_id IS NULL) AND (people.organization_id NOT IN ('4','5','6') OR people.organization_id IS NULL))",
                        "people.is_deleted = 0"
                    ],
                    'wheres_any' => []
                ]
            ],
            // is groups [1,2]
            [
                [1,2],
                PersonSearch::OP_IS,
                [1,2,3],
                [4,5,6],
                [
                    'joins' => [],
                    'wheres' => [
                        "(people.id IN ('1','2','3') OR people.organization_id IN ('4','5','6'))",
                        "people.is_deleted = 0"
                    ],
                    'wheres_any' => []
                ]
            ],
            // is groups [1,2] and a lot of users
            [
                [1,2],
                PersonSearch::OP_IS,
                array_fill(0, 1001, mt_rand(1, 1000)),
                [4,5,6],
                [
                    'joins' => [
                        [
                            'person2usergroups',
                            'LEFT JOIN person2usergroups AS j_1 ON (j_1.person_id = people.id)'
                        ]
                    ],
                    'wheres' => [
                        "(j_1.usergroup_id IN ('1','2') OR people.organization_id IN ('4','5','6'))",
                        "people.is_deleted = 0"
                    ],
                    'wheres_any' => []
                ]
            ],
            // not groups [1,2] And there are no organizations
            [
                [1,2],
                PersonSearch::OP_NOT,
                [1,2,3],
                [],
                [
                    'joins' => [],
                    'wheres' => [
                        "(people.id NOT IN ('1','2','3') OR people.id IS NULL)",
                        "people.is_deleted = 0"
                    ],
                    'wheres_any' => []
                ]
            ],
            // is groups [1,2] And there are no organizations
            [
                [1,2],
                PersonSearch::OP_IS,
                [1,2,3],
                [],
                [
                    'joins' => [],
                    'wheres' => [
                        "people.id IN ('1','2','3')",
                        "people.is_deleted = 0"
                    ],
                    'wheres_any' => []
                ]
            ],
            // not groups [1,2] and a lot of users and there are no organizations
            [
                [1,2],
                PersonSearch::OP_NOT,
                array_fill(0, 1001, mt_rand(1, 1000)),
                [],
                [
                    'joins' => [
                        [
                            'person2usergroups',
                            'LEFT JOIN person2usergroups AS j_1 ON (j_1.person_id = people.id)'
                        ]
                    ],
                    'wheres' => [
                        "(j_1.usergroup_id NOT IN ('1','2') OR j_1.usergroup_id IS NULL)",
                        "people.is_deleted = 0"
                    ],
                    'wheres_any' => []
                ]
            ],
            // is groups [1,2] and a lot of users and there are no organizations
            [
                [1,2],
                PersonSearch::OP_IS,
                array_fill(0, 1001, mt_rand(1, 1000)),
                [],
                [
                    'joins' => [
                        [
                            'person2usergroups',
                            'LEFT JOIN person2usergroups AS j_1 ON (j_1.person_id = people.id)'
                        ]
                    ],
                    'wheres' => [
                        "j_1.usergroup_id IN ('1','2')",
                        "people.is_deleted = 0"
                    ],
                    'wheres_any' => []
                ]
            ]
        ];
    }

    /**
     * @dataProvider searchByUserGroupDataProvider
     */
    public function testSearchByUserGroup(
        $groupIds,
        $operation,
        $expectedUserIds,
        $expectedOrganizationIds,
        $expectedSqlParts
    )
    {
        // GIVEN
        $utilMock = \Mockery::mock('alias:Orb\Util\Util');
        $utilMock->shouldReceive('requestUniqueId')->andReturn(1);

        $dbReaderMock = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->setMethods(['fetchAllCol', 'quote'])
            ->getMock();
        $dbReaderMock->expects(self::any())->method('fetchAllCol')->will($this->onConsecutiveCalls(
            $expectedUserIds,
            $expectedOrganizationIds
        ));
        $dbReaderMock->expects(self::any())->method('quote')->willReturnCallback(function ($value){
            return sprintf("'%s'", $value);
        });

        $containerMock = $this->getMockBuilder(DeskproContainer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDbRead', 'getDb'])
            ->getMock();
        $containerMock->expects(self::any())->method('getDbRead')->willReturn($dbReaderMock);
        $containerMock->expects(self::any())->method('getDb')->willReturn($dbReaderMock);
        App::$container = $containerMock;

        $personSearch = new PersonSearch();
        $personSearch->addTerm(PersonSearch::TERM_USERGROUP, $operation, $groupIds);

        // WHEN/THEN
        $this->assertEquals($expectedSqlParts, $personSearch->getSqlParts(), "Wrong sqlParts");
    }
}
