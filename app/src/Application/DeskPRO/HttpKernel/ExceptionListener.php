<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage HttpKernel
 */

namespace Application\DeskPRO\HttpKernel;

use Application\DeskPRO\App;
use Application\DeskPRO\Exception\ValidationException;
use DeskPRO\Kernel\KernelErrorHandler;
use Orb\Util\Strings;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ExceptionListener
{
    /** @var \Exception|null */
    protected $last_exception = null;
    /** @var bool */
    private $handling_exception = false;

    public function getLastException()
    {
        return $this->last_exception;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ($this->handling_exception === true) return;
        $this->handling_exception = true;

        $exception = $event->getException();
        $this->_logException($exception);

        // This is fetched from the template
        $this->last_exception = $exception;

        $this->handling_exception = false;
    }

    protected function _logException(\Exception $exception)
    {
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            $this->logRequestException('not_found', $exception);

            return;
        }
        if ($exception instanceof ValidationException && defined('DP_INTERFACE') && DP_INTERFACE == 'api') {
            return;
        }

        if ($exception instanceof \Application\DeskPRO\HttpKernel\Exception\NoPermissionException) {
            $this->logRequestException('no_permission', $exception);

            return;
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
            $this->logRequestException('bad_method', $exception);

            return;
        }

        $exception->_dp_sn = KernelErrorHandler::genSessionName();

        $errinfo = KernelErrorHandler::getExceptionInfo($exception);
        KernelErrorHandler::logErrorInfo($errinfo);
    }

    private function logRequestException($type, \Exception $e)
    {
        if (!dp_get_config('enable_request_errorlog')) {
            return;
        }

        $log_file = dp_get_log_dir() . '/request_errors.log';

        $url = '';
        if (defined('DP_REQUEST_URL')) {
            $url = DP_REQUEST_URL;
        } elseif (defined('DP_INTERFACE')) {
            $url = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
            if (class_exists('Application\\DeskPRO\\App', false)) {
                try {
                    $url = App::getRequest()->getUri();
                } catch (\Exception $e) {}
            }
        }

        $top = sprintf("[%s] %s: %s", date('Y-m-d H:i:s'), $type, $url);
        $lines = sprintf("Type: %s\nException: %s %s\n%s", get_class($e), $e->getCode(), $e->getMessage(), KernelErrorHandler::formatBacktrace($e->getTrace()));
        $lines = Strings::modifyLines($lines, "\t");

        @file_put_contents($log_file, $top . "\n" . $lines, \FILE_APPEND);
    }
}
