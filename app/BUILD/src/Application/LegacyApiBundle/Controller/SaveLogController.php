<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DpSys\LowError\SystemErrorHandler;

/**
 * @ApiModes("all")
 */
class SaveLogController extends AbstractController
{
    public function logJsErrorAction()
    {
        $message     = $this->in->getString('message');
        $script_file = $this->in->getString('script_file');
        $script_line = $this->in->getUint('script_line');
        $trace       = $this->in->getString('trace');
        $context     = $this->in->getArrayValue('context');

        $url = '';
        if (isset($context['url'])) {
            $url = $context['url'];
            unset($context['url']);
        }

        $einfo = [
            'type'              => 'error',
            'pri'               => 'NOTICE',
            'summary'           => $message,
            'errname'           => 'JSError',
            'errfile'           => $script_file,
            'errline'           => $script_line,
            'errstr'            => $message,
            'time_to_error'     => '0',
            'url'               => $url,
            'client_user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'context_data'      => print_r($context, true),
            'trace'             => $trace,
            'session_name'      => DP_REQUEST_ID,
            'die'               => false,
            'display'           => false,
            'process_log'       => '',
        ];

        @SystemErrorHandler::logErrorInfo($einfo);

        return $this->createSuccessResponse();
    }
}
