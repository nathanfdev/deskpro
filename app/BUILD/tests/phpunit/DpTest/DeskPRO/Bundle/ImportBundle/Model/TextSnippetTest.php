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
