<?php

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
