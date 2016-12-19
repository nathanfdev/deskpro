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
 *
 * @category Entities
 */

namespace deskpro_jira\RequestHandler;

use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestHandlerInterface;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\JIRA\ApiCoreException;
use Application\DeskPRO\JIRA\ApiErrorsException;
use Application\DeskPRO\JIRA\OAuthWrapper;
use Application\DeskPRO\Service\JIRA;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PackageRequestHandler implements ApiPackageRequestHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handleApiPackageRequest(ApiPackageRequestContext $context)
    {
        switch ($context->getAction()) {
            case 'get-meta':
                return $this->getMetaAction($context);
            default:
                throw $context->createNotFoundException();
        }
    }

    /**
     * check api link connection.
     *
     * @param DeskproContainer $container
     *
     * @return array|null
     */
    protected function checkErrors(DeskproContainer $container)
    {
        $error = [];

        /** @var JIRA $js */
        $js   = $container->get(JIRA::NAME);
        $back = $container->getRouter()->generate('jira_token', [], UrlGeneratorInterface::ABSOLUTE_URL);
        try {
            $oauth = new OAuthWrapper($js, $back);
            $oauth->requestTempCredentials();
        } catch (\Exception $e) {
            $error = [
                'type'    => 'other',
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ];

            if ($e instanceof ClientException) {
                $error['type'] = 'curl';
            } elseif ($e instanceof ApiCoreException || $e instanceof ApiErrorsException) {
                $error['type'] = 'jira';
            } elseif ($e->getCode() >= 1000) {
                $error['type'] = 'app';
            }
        }

        if (!$error && !$js->getTokens()) {
            $error['token'] = true;
        }

        return $error ?: null;
    }

    /**
     * @param ApiPackageRequestContext $context
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getMetaAction(ApiPackageRequestContext $context)
    {
        if ($error = $this->checkErrors($context->getContainer())) {
            return $context->createJsonResponse(['error' => $error]);
        }

        /** @var JIRA $js */
        $js   = $context->getContainer()->get(JIRA::NAME);
        $data = (array) $context->getIn()->getAll('req');
        $meta = $js->updateMeta($data);

        return $context->createJsonResponse($meta->toArray());
    }
}
