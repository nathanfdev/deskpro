<?php

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
            $db->update($t, ['status' => 'deleted', 'is_reviewed' => 1], ['validating' => '1']);
            $db->update($t, ['status' => 'deleted', 'is_reviewed' => 1], ['status' => 'user_validating']);
            $db->update($t, ['status' => 'deleted', 'is_reviewed' => 1], ['status' => 'validating']);
            $db->update($t, ['status' => 'deleted', 'is_reviewed' => 1], ['status' => 'tmp']);
            $db->update($t, ['status' => 'deleted', 'is_reviewed' => 1], ['status' => 'deleted']);
        }
    }
}

//[[build:1460678408]]
