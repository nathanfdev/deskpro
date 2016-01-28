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

namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0021_validatingpublish extends AbstractBuild
{
    public function run()
    {
        $db = $this->container->getDb();

        $this->out('Resetting status on validating feedback');

        $db->update('feedback', [
            'status'        => 'hidden',
            'hidden_status' => 'deleted',
        ], ['validating' => '1']);

        foreach (['validating', 'user_validating', 'temp'] as $s) {
            $db->update('feedback', [
                'status'        => 'hidden',
                'hidden_status' => 'deleted',
            ], ['status' => 'hidden', 'hidden_status' => $s]);
        }

        $this->out('Resetting status on validating comments');

        foreach (['article_comments', 'download_comments', 'feedback_comments', 'news_comments'] as $t) {
            $db->update($t, ['status' => 'deleted'], ['validating' => '1']);
            $db->update($t, ['status' => 'deleted'], ['status' => 'user_validating']);
            $db->update($t, ['status' => 'deleted'], ['status' => 'validating']);
            $db->update($t, ['status' => 'deleted'], ['status' => 'tmp']);
        }
    }
}

//[[build:1456790407]]

