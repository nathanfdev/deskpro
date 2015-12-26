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

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class ErrorController extends AbstractController
{
    public function showExceptionAction(FlattenException $exception, $logger = null)
    {
        $code     = $exception->getStatusCode();
        $template = $this->makeTemplateName($code);

        // if an exception occured BEFORE the security (Firewall) listener runs, then
        // there is no token in storage. Our templates usually do is_granted type checks,
        // so we need to do something to avoid the is_granted throwing its own exception while we render this page.
        // Ideally, error templates would not contain security checks, but in practice they will, so this
        // helps to at least show a nice error, even if the security context is missing.
        // NOTE: this should be rather rare. the FirewallListener runs early. However,
        //       the router runs before it so we'd have problems in 404s.
        //       That said, we patched that by using the notFoundAction on this controller
        //       to force the firewall to run before we throw the 404 exceptions.
        if (!$this->getUser()) {
            $this->get('security.token_storage')->setToken(new AnonymousToken('anon.', 'anon.'));
        }

        // if the message is "Something has intentionally gone wrong." its the dev route /_error/{code} being vistited for a test
        if ($this->container->getParameter('kernel.debug') && $exception->getMessage() !== 'Something has intentionally gone wrong.') {
            return $this->render('TwigBundle:Exception:exception_full.html.twig', array(
                'status_code'    => $code,
                'status_text'    => isset(Response::$statusTexts[$code]) ? Response::$statusTexts[$code] : '',
                'exception'      => $exception,
                'logger'         => $logger,
                'currentContent' => null,
            ));
        }

        $request  = Request::createFromGlobals();
        $base_url = $request->getBaseUrl();

        try {
            return $this->renderThemeView(
                $template,
                array(
                    'base_url'    => $base_url,
                    'status_code' => $code,
                    'status_text' => isset(Response::$statusTexts[$code]) ? Response::$statusTexts[$code] : '',
                    'exception'   => $exception,
                )
            );
        } catch (\Exception $e) {
            // if that is not found, use the default for this status code (error.html.twig or exception.html.twig)
            $template = $this->makeTemplateName($code, true);

            return $this->renderThemeView(
                $template,
                array(
                    'base_url'    => $base_url,
                    'status_code' => $code,
                    'status_text' => isset(Response::$statusTexts[$code]) ? Response::$statusTexts[$code] : '',
                    'exception'   => $exception,
                )
            );
        }
    }

    public function notFoundAction($path)
    {
        // this is a portal catch all route. Anything that ends up here was not matched by the router.
        // we have this so that 404s hit a controller (meaning all request listeners were run)
        // and this ensures that FirewallListener populates our security token.
        throw new NotFoundHttpException('could not find a route for: '.$path);
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
     *
     * @return string
     */
    public function makeTemplateName($code = null, $force_use_default = false)
    {
        $tpl_prefix = 'Theme:Error';

        $tpl_start = ($code >= 500 ? 'exception' : 'error');

        return sprintf('%s:%s%s.html.twig', $tpl_prefix, $tpl_start, ($code && !$force_use_default) ? $code : '');
    }
}
