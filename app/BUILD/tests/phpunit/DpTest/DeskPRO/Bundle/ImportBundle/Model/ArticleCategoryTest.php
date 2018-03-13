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

namespace DpTest\DeskPRO\Bundle\ImportBundle\Model;

use DeskPRO\Bundle\ImportBundle\Model\ArticleCategory;

/**
 * Class ArticleCategoryTest.
 */
class ArticleCategoryTest extends AbstractModelTest
{
    protected static $modelClass = ArticleCategory::class;

    public function test_required_params_validation()
    {
        $errors = $this->validateData([]);

        $this->assertCount(2, $errors);
        $this->assertEquals('title', $errors[0]->getPropertyPath());
        $this->assertEquals('raw_data', $errors[1]->getPropertyPath());
    }

    public function test_sub_category_validation()
    {
        $errors = $this->validateData([
            'title'      => 'Cat 1',
            'categories' => [
                [],
            ],
        ]);

        $this->assertCount(1, $errors);
        $this->assertEquals('categories[0].title', $errors[0]->getPropertyPath());
    }

    public function test_full_params()
    {
        $params = [
            'title'       => 'Category 1',
            'is_agent'    => true,
            'is_book'     => true,
            'user_groups' => ['registered'],
            'categories'  => [
                [
                    'oid'         => '2',
                    'title'       => 'Sub Category 1',
                    'is_agent'    => true,
                    'is_book'     => true,
                    'user_groups' => ['everyone'],
                    'categories'  => [
                        [
                            'oid'         => '3',
                            'title'       => 'Sub Category 1a',
                            'is_agent'    => true,
                            'is_book'     => true,
                            'user_groups' => [],
                            'categories'  => [],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($this->transformData($params), $params);
    }
}
