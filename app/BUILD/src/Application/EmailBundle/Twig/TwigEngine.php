<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Twig;

use Application\EmailBundle\Twig\PostRenderFilter\EmailPostRenderFilter;
use DpSys\LowError\SystemErrorHandler;

class TwigEngine extends \Symfony\Bundle\TwigBundle\TwigEngine
{
    public function render($name, array $parameters = [])
    {
        // An object so that sets against it are
        // persisted across blocks in the same template
        if (!isset($parameters['tplvars'])) {
            $parameters['tplvars'] = new \stdClass();
        }

        $is_custom_template = $this->environment->isCustomTemplate($name);
        if (!$is_custom_template) {
            $code = parent::render($name, $parameters);

            if (strpos($name, 'DeskPRO:emails_') !== false) {
                $proc = new EmailPostRenderFilter();
                $code = $proc->process($name, $code);
            }

            return $code;
        } else {
            try {
                $GLOBALS['DP_IS_RENDERING_TPL'] = true;
                $code                           = parent::render($name, $parameters);
                if (strpos($name, 'DeskPRO:emails_') !== false) {
                    $proc = new EmailPostRenderFilter();
                    $code = $proc->process($name, $code);
                }
                $GLOBALS['DP_IS_RENDERING_TPL'] = false;

                return $code;
            } catch (\Twig_Error_Syntax $e) {
                $GLOBALS['DP_IS_RENDERING_TPL'] = false;
                $exception                      = $e;
            } catch (\Twig_Error_Runtime $e) {
                $GLOBALS['DP_IS_RENDERING_TPL'] = false;
                $exception                      = $e;
            } catch (\Exception $e) {
                $GLOBALS['DP_IS_RENDERING_TPL'] = false;
                throw $e;
            }

            $errinfo                  = SystemErrorHandler::getExceptionInfo($exception);
            $errinfo['no_send_error'] = true;
            SystemErrorHandler::logErrorInfo($errinfo);

            try {
                return $this->render($name, $parameters);
            } catch (\Twig_Error_Loader $e) {
                // Means there was only ever the custom one,
                // so lets just throw the original exception up
                throw $exception;
            }
        }
    }
}
