<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Model;

use DeskPRO\Bundle\ImportBundle\Model\TextSnippet;

/**
 * Class TextSnippetTest.
 */
class TextSnippetTest extends AbstractModelTest
{
    protected static $modelClass = TextSnippet::class;

    public function test_required_params_validation()
    {
        $errors = $this->validateData([]);

        $this->assertCount(5, $errors);
        $this->assertEquals('category', $errors[0]->getPropertyPath());
        $this->assertEquals('shortcutCode', $errors[1]->getPropertyPath());
        $this->assertEquals('titleTranslations', $errors[2]->getPropertyPath());
        $this->assertEquals('snippetTranslations', $errors[3]->getPropertyPath());
        $this->assertEquals('raw_data', $errors[4]->getPropertyPath());
    }

    public function test_required_params()
    {
        $params = [
            'category'           => 1,
            'shortcut_code'      => 'code',
            'title_translations' => [
                [
                    'language' => 'eng',
                    'value'    => 'text',
                ],
            ],
            'snippet_translations' => [
                [
                    'language' => 'eng',
                    'value'    => 'text',
                ],
            ],
        ];

        $this->assertEquals($this->transformData($params), array_merge($params, [
            'is_draft' => false,
        ]));
    }

    public function test_full_params()
    {
        $params = [
            'person'             => 1,
            'category'           => 1,
            'shortcut_code'      => 'code',
            'is_draft'           => true,
            'title_translations' => [
                [
                    'language' => 'eng',
                    'value'    => 'text',
                ],
            ],
            'snippet_translations' => [
                [
                    'language' => 'eng',
                    'value'    => 'text',
                ],
            ],
        ];

        $this->assertEquals($this->transformData($params), $params);
    }
}
