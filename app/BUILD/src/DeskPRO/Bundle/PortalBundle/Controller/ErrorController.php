<?php

namespace DeskPRO\Bundle\PortalBundle\Controller;

use DeskPRO\Bundle\PortalBundle\Controller\Api\AbstractApiController;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use DpSys\LowError\SystemErrorHandler;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

/**
 * Class ErrorController.
 */
class ErrorController extends AbstractController
{
    /**
     * @param FlattenException $exception
     * @param null             $logger
     *
     * @return bool|Response
     */
    public function showExceptionAction(FlattenException $exception, $logger = null)
    {
        // detect recursions
        // in some edge cases we can get exceptions in sub requests (using in http cache)
        // so it causes infinity recursion loops
        $requestStack = $this->get('request_stack');
        $appEnv       = $this->get('deskpro.app_env');

        $reflection = new \ReflectionClass(RequestStack::class);
        $property   = $reflection->getProperty('requests');
        $property->setAccessible(true);

        $requests = $property->getValue($requestStack);
        if (count($requests) > 2) {
            return new Response($appEnv->isDebug() ? $exception->getMessage() : '', $exception->getStatusCode());
        }

        // don't render errors for page fragments
        $tagRequests = array_filter($requests, function (Request $request) {
            return $request instanceof TagRequest;
        });

        if (count($tagRequests) > 0) {
            // an error here means we're an error inside of rendering a tag (either inline or esi)
            // Any 500 type error will have been logged by the exception handler, and any 300s or 400s
            // are typically caused by permission checks or permission errors.
            // The tag processor or esi renderer will ignore these already (See TagProcessor, PortalHttpCache),
            // but we need to avoid rendering an error template here anyway because it can cause an infinite loop.
            // E.g.:
            //  1. Sidebar tries to render articles
            //  2. No permission to use articles, so that tag throws an AccessDeniedHttpException
            //  3. That is caught by the exception listener and calls this controller
            //  4. This controller renders an access denied template
            //  5. ... which draws the sidebar
            //  6. ... which tries to render articles again...etc until timeout
            // So the only thing to do really is to return an empty response. The user never sees it because this is
            // just a fragment of a parent page.
            return new Response($appEnv->isDebug() ? $exception->getMessage() : '', $exception->getStatusCode());
        }

        // check if the error controller was already called
        // allow rendering error template only for the first exception
        if ($appEnv->hasRuntimeVar('portal.error_controller_called')) {
            return new Response($appEnv->isDebug() ? $appEnv->getRuntimeVar('portal.error_controller_called').' '.$exception->getMessage() : '', $exception->getStatusCode());
        } else {
            $appEnv->setRuntimeVar('portal.error_controller_called', $exception->getMessage());
        }

        if ($response = $this->delegateApi($exception)) {
            return $response;
        }

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
        if (
            $this->container->getParameter('kernel.debug')
            && $exception->getMessage() !== 'Something has intentionally gone wrong.'
            && $exception->getStatusCode() >= 500
        ) {
            return $this->render('TwigBundle:Exception:exception_full.html.twig', [
                'status_code'    => $code,
                'status_text'    => isset(Response::$statusTexts[$code]) ? Response::$statusTexts[$code] : '',
                'exception'      => $exception,
                'logger'         => $logger,
                'currentContent' => null,
            ]);
        }

        $request = Request::createFromGlobals();
        $baseUrl = $request->getBaseUrl();

        try {
            return $this->renderThemeView(
                $template,
                [
                    'base_url'    => $baseUrl,
                    'status_code' => $code,
                    'status_text' => isset(Response::$statusTexts[$code]) ? Response::$statusTexts[$code] : '',
                    'exception'   => $exception,
                ]
            );
        } catch (\Exception $e) {
            try {
                // if that is not found, use the default for this status code (error.html.twig or exception.html.twig)
                $template = $this->makeTemplateName($code, true);

                return $this->renderThemeView(
                    $template,
                    [
                        'base_url'    => $baseUrl,
                        'status_code' => $code,
                        'status_text' => isset(Response::$statusTexts[$code]) ? Response::$statusTexts[$code] : '',
                        'exception'   => $exception,
                    ]
                );
            } catch (\Exception $e) {
                SystemErrorHandler::logException($e, false, null, true);

                return new Response($exception->getMessage(), $exception->getStatusCode());
            }
        }
    }

    /**
     * @param string $path
     */
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
     * @param int  $code
     * @param bool $force_use_default
     *
     * @return string
     */
    public function makeTemplateName($code = null, $force_use_default = false)
    {
        $tpl_prefix = 'Theme:Error';

        $tpl_start = ($code >= 500 ? 'exception' : 'error');

        return sprintf('%s:%s%s.html.twig', $tpl_prefix, $tpl_start, ($code && !$force_use_default) ? $code : '');
    }

    /**
     * @param FlattenException $exception
     *
     * @return bool
     */
    protected function delegateApi(FlattenException $exception)
    {
        // Let's try to figure out if this Controller was from API
        try {
            $controller = $this->get('request_stack')->getMasterRequest()->attributes->get('_controller');
            $parts      = explode('::', $controller);
            $reflection = new \ReflectionClass($parts[0]);
        } catch (\Exception $e) {
            return false;
        }

        if ($reflection->isSubclassOf(AbstractApiController::class)) {
            $request = $this->get('request_stack')->getCurrentRequest()->duplicate(null, null,
                [
                    '_controller' => 'DeskPRO\Bundle\ApiBundle\Controller\ExceptionController::showAction',
                    'exception'   => $exception,
                ]
            );

            return $this->get('kernel')->handle($request, HttpKernelInterface::SUB_REQUEST, false);
        }

        return false;
    }
}
