<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Model;

use DeskPRO\Bundle\ImportBundle\Model\TextSnippetCategory;

/**
 * Class TextSnippetCategoryTest.
 */
class TextSnippetCategoryTest extends AbstractModelTest
{
    protected static $modelClass = TextSnippetCategory::class;

    public function test_required_params_validation()
    {
        $errors = $this->validateData([]);

        $this->assertCount(3, $errors);
        $this->assertEquals('typename', $errors[0]->getPropertyPath());
        $this->assertEquals('titleTranslations', $errors[1]->getPropertyPath());
        $this->assertEquals('raw_data', $errors[2]->getPropertyPath());
    }

    public function test_required_params()
    {
        $params = [
            'typename'           => 'tickets',
            'title_translations' => [
                [
                    'language' => 'eng',
                    'value'    => 'text',
                ],
            ],
        ];

        $this->assertEquals($this->transformData($params), array_merge($params, [
            'is_global' => false,
        ]));
    }

    public function test_full_params()
    {
        $params = [
            'person'             => 1,
            'typename'           => 'tickets',
            'is_global'          => true,
            'title_translations' => [
                [
                    'language' => 'eng',
                    'value'    => 'text',
                ],
                [
                    'language' => 'fr',
                    'value'    => 'text',
                ],
            ],
        ];

        $this->assertEquals($this->transformData($params), $params);
    }
}
