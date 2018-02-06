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

use DeskPRO\Bundle\ImportBundle\Model\Download;

/**
 * Class DownloadTest.
 */
class DownloadTest extends AbstractModelTest
{
    protected static $modelClass = Download::class;

    public function test_required_params_validation()
    {
        $errors = $this->validateData([]);

        $this->assertCount(5, $errors);
        $this->assertEquals('title', $errors[0]->getPropertyPath());
        $this->assertEquals('content', $errors[1]->getPropertyPath());
        $this->assertEquals('status', $errors[2]->getPropertyPath());
        $this->assertEquals('raw_data', $errors[3]->getPropertyPath());
        $this->assertEquals('blob', $errors[4]->getPropertyPath());
    }

    public function test_required_params()
    {
        $params = [
            'title'   => 'Download 1',
            'content' => 'Download content',
            'status'  => 'published',
            'blob'    => [
                'blob_url'     => 'http://url',
                'file_name'    => 'file.jpg',
                'content_type' => 'image/jpg',
            ],
        ];

        $this->assertEquals($this->transformData($params), array_merge($params, [
            'view_count'    => 0,
            'num_downloads' => 0,
            'labels'        => [],
        ]));
    }

    public function test_full_params()
    {
        $params = [
            'title'   => 'Download 1',
            'content' => 'Download content',
            'status'  => 'archived',
            'person'  => '1',
            'blob'    => [
                'blob_url'     => 'http://url',
                'file_name'    => 'file.jpg',
                'content_type' => 'image/jpg',
            ],
            'view_count'     => 10,
            'num_downloads'  => 20,
            'labels'         => ['label 1', 'label 2'],
            'category'       => 'Download category 1',
            'language'       => 'eng',
            'date_created'   => '2016-07-15T12:55:01+0300',
            'date_published' => '2016-07-15T12:55:01+0300',
        ];

        $this->assertEquals($this->transformData($params), $params);
    }
}
