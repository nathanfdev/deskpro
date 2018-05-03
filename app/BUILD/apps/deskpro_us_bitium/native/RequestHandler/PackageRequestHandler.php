<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_us_bitium\RequestHandler;

use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestHandlerInterface;

class PackageRequestHandler implements ApiPackageRequestHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handleApiPackageRequest(ApiPackageRequestContext $context)
    {
        switch ($context->getAction()) {
            case 'check-requirements':
                return $this->checkRequirementsAction($context);
            default:
                throw $context->createNotFoundException();
        }
    }

    /**
     * @param ApiPackageRequestContext $context
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkRequirementsAction(ApiPackageRequestContext $context)
    {
        $fulfilled = function_exists('mcrypt_create_iv') || function_exists('openssl_cipher_iv_length');

        return $context->createJsonResponse([
            'success' => $fulfilled,
            'error'   => 'This application requires PHP with the OpenSSL or mcrypt extension installed. This app will NOT WORK until you install one of those extensions.',
        ]);
    }
}
