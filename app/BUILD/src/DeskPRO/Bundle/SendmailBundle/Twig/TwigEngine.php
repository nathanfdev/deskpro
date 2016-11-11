<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\SendmailBundle\Twig;

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

        $isCustomTemplate = $this->environment->isCustomTemplate($name);
        if (!$isCustomTemplate) {
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
