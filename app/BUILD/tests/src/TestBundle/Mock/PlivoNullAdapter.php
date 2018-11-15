<?php

namespace DpTestSrc\TestBundle\Mock;

use DeskPRO\Bundle\AppBundle\Entity\PlivoVoiceAccount;
use DeskPRO\Bundle\VoiceBundle\Plivo\PlivoAdapter;

/**
 * Class PlivoNullAdapter.
 */
class PlivoNullAdapter extends PlivoAdapter
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getAccount(PlivoVoiceAccount $account)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function createApplication(PlivoVoiceAccount $account, $appName, $answerUrl, $answerMethod, $hangupUrl, $hangupMethod)
    {
        return 1;
    }
}
