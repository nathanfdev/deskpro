<?php

namespace DpSys\LowScript;

/**
 * Class PingTaskRouterWorker.
 */
class PingTaskRouterWorker extends LowScriptAbstract
{
    use VoiceTaskRouterTrait;

    /**
     * {@inheritdoc}
     */
    protected function runAction()
    {
        $agentSession = $this->getAgentSession();
        $agentId      = $agentSession['person_id'];

        $this->pingTaskRouterWorker($agentId, isset($_REQUEST['has_voice']) && $_REQUEST['has_voice']);

        $data = [];
        if ($this->getSetting('beta_features.voice')) {
            $data['task_router_workers'] = $this->getVoiceWorkersActivity();
        }

        header('Content-Type: application/json');

        echo json_encode($data);
    }
}
