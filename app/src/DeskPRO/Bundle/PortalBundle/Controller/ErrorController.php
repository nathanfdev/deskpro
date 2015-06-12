<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
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
 */

namespace DeskPRO\Bundle\PortalBundle\Controller;


use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;

class ErrorController extends AbstractController
{
    public function showExceptionAction(FlattenException $exception)
    {
        $code = $exception->getStatusCode();

        // try for status code specific template
        $template = $this->makeTemplateName($code);
        if (!$this->templateExists($template)) {
            // if that is not found, use the default for this status code (error.html.twig or exception.html.twig)
            $template = $this->makeTemplateName($code, true);
        }

        return $this->renderThemeView(
            $template,
            array(
                'status_code' => $code,
                'status_text' => isset(Response::$statusTexts[$code]) ? Response::$statusTexts[$code] : '',
                'exception' => $exception
            )
        );
    }

    // to be removed when the minimum required version of Twig is >= 2.0
    protected function templateExists($template)
    {
        $loader = $this->get('twig')->getLoader();
        if ($loader instanceof \Twig_ExistsLoaderInterface) {
            return $loader->exists($template);
        }

        try {
            $loader->getSource($template);

            return true;
        } catch (\Twig_Error_Loader $e) {
        }

        return false;
    }

    /**
     * @param $code
     * @return string
     */
    public function makeTemplateName($code = null, $force_use_default = false)
    {
        $tpl_prefix = 'Theme:Error';

        $tpl_start = ($code >= 500 ? 'exception' : 'error');

        return sprintf('%s:%s%s.html.twig', $tpl_prefix, $tpl_start, ($code && !$force_use_default) ? $code : '');
    }
}
