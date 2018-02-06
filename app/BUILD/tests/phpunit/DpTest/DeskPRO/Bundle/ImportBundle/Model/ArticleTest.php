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

use DeskPRO\Bundle\ImportBundle\Model\Article;

/**
 * Class ArticleTest.
 */
class ArticleTest extends AbstractModelTest
{
    protected static $modelClass = Article::class;

    public function test_required_params_validation()
    {
        $errors = $this->validateData([]);

        $this->assertCount(4, $errors);
        $this->assertEquals('title', $errors[0]->getPropertyPath());
        $this->assertEquals('content', $errors[1]->getPropertyPath());
        $this->assertEquals('status', $errors[2]->getPropertyPath());
        $this->assertEquals('raw_data', $errors[3]->getPropertyPath());
    }

    public function test_required_params()
    {
        $params = [
            'title'   => 'Article 1',
            'content' => 'Article content',
            'status'  => 'published',
        ];

        $this->assertEquals($this->transformData($params), array_merge($params, [
            'view_count'           => 0,
            'labels'               => [],
            'categories'           => [],
            'comments'             => [],
            'attachments'          => [],
            'title_translations'   => [],
            'content_translations' => [],
            'custom_fields'        => [],
        ]));
    }

    public function test_full_params()
    {
        $params = [
            'title'      => 'Article 1',
            'content'    => 'Article content',
            'status'     => 'published',
            'person'     => '1',
            'view_count' => 100,
            'labels'     => ['label 1', 'label 2'],
            'categories' => ['Category 1', 'Category 2'],
            'comments'   => [
                [
                    'oid'          => '1',
                    'content'      => 'Comment content',
                    'person'       => '2',
                    'status'       => 'visible',
                    'is_reviewed'  => true,
                    'date_created' => '2016-07-15T12:55:01+0300',
                ],
            ],
            'attachments' => [
                [
                    'oid'          => '1',
                    'person'       => '1',
                    'blob_url'     => 'http://url',
                    'file_name'    => 'file.jpg',
                    'content_type' => 'image/jpg',
                    'is_inline'    => true,
                ],
            ],
            'title_translations' => [
                [
                    'language' => 'fr',
                    'value'    => 'translated text',
                ],
                [
                    'language' => 'it',
                    'value'    => 'translated text',
                ],
            ],
            'content_translations' => [
                [
                    'language' => 'fr',
                    'value'    => 'translated text',
                ],
                [
                    'language' => 'it',
                    'value'    => 'translated text',
                ],
            ],
            'custom_fields' => [
                [
                    'oid'   => 1,
                    'value' => 'val',
                ],
                [
                    'name'  => 'Field 1',
                    'value' => 'val 2',
                ],
            ],
            'end_action'     => 'archive',
            'date_end'       => '2016-07-15T12:55:01+0300',
            'date_created'   => '2016-07-15T12:55:01+0300',
            'date_published' => '2016-07-15T12:55:01+0300',
            'language'       => 'eng',
        ];

        $this->assertEquals($this->transformData($params), $params);
    }
}
