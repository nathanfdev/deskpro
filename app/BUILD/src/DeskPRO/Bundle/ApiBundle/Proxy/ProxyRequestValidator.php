<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Proxy;

use DeskPRO\Component\Util\RegexUtils;
use DeskPRO\Component\Util\StringUtils;
use League\Url\Url;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ProxyRequestValidator.
 */
class ProxyRequestValidator
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * Constructor.
     *
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param ProxyRequestInterface $request
     */
    public function validate(ProxyRequestInterface $request)
    {
        if ($request instanceof ApplicationProxyRequest) {
            $this->validateWhitelistableRequest($request);
        }

        $this->validateProxyRequest($request);
    }

    public function validateProxyRequest(ProxyRequestInterface $request)
    {
        if (!$request->getProxyUrl()) {
            throw new \RuntimeException('No proxy url provided.');
        }

        $errors = $this->validator->validate($request->getProxyUrl(), [new Assert\Url()]);
        if (count($errors)) {
            throw new \RuntimeException('The proxy url is not valid.');
        }

        try {
            Url::createFromUrl($request->getProxyUrl());
        } catch (\Exception $e) {
            throw new \RuntimeException('Unable to parse proxy url.');
        }
    }

    /**
     * @param ApplicationProxyRequest $request
     */
    public function validateWhitelistableRequest( ApplicationProxyRequest $request)
    {
        $this->validateProxyRequest($request);

        if (empty($request->getWhiteList())) {
            throw new \RuntimeException('No proxy whitelist is defined.');
        }

        $proxyUrl = Url::createFromUrl($request->getProxyUrl());

        $baseProxyUrl = $proxyUrl->getBaseUrl().$proxyUrl->getPath()->getUriComponent();

        foreach ($request->getWhiteList() as $urlPattern) {
            if (preg_match('#^/(.+)/$#', $urlPattern, $m)) {
                $urlPattern = $m[1];

                if (RegexUtils::safePregMatch("#$urlPattern#", $baseProxyUrl)) {
                    return;
                }
            } elseif (preg_match('#(.+)\*$#', $urlPattern, $m)) {
                if (StringUtils::startsWith($m[1], $baseProxyUrl)) {
                    return;
                }
            } elseif ($urlPattern === $baseProxyUrl) {
                return;
            }
        }

        throw new \RuntimeException(sprintf(
            'The proxy url is not allowed (allowed %s).',
            implode(', ', $request->getWhiteList())
        ));
    }

}
