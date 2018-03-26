<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Model;

use DeskPRO\Bundle\ImportBundle\Model\News;

/**
 * Class NewsTest.
 */
class NewsTest extends AbstractModelTest
{
    protected static $modelClass = News::class;

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
            'title'   => 'News 1',
            'content' => 'News content',
            'status'  => 'published',
        ];

        $this->assertEquals($this->transformData($params), array_merge($params, [
            'labels'     => [],
            'view_count' => 0,
        ]));
    }

    public function test_full_params()
    {
        $params = [
            'title'          => 'News 1',
            'content'        => 'News content',
            'language'       => 'eng',
            'status'         => 'archived',
            'labels'         => ['label 1', 'label 2'],
            'view_count'     => '0',
            'date_created'   => '2016-07-15T12:55:01+0300',
            'date_published' => '2016-07-15T12:55:01+0300',
            'category'       => 'News category 1',
            'person'         => 1,
        ];

        $this->assertEquals($this->transformData($params), $params);
    }
}
