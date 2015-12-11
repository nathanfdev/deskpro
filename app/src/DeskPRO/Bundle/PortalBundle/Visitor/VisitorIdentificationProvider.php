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
namespace DeskPRO\Bundle\PortalBundle\Visitor;

use DeskPRO\Component\Util\RandUtils;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class VisitorIdentificationProvider
{
    const COOKIE_NAME    = 'dp__v';
    const ATTRIBUTE_NAME = 'visitor_id';

    /**
     * @var RequestStack
     */
    private $request_stack;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * VisitorIdentificationProvider constructor.
     *
     * @param RequestStack    $request_stack
     * @param LoggerInterface $logger
     */
    public function __construct(RequestStack $request_stack, LoggerInterface $logger)
    {
        $this->request_stack = $request_stack;
        $this->logger        = $logger;
    }

    /**
     * @return string
     */
    public static function generateRandomIdentifier()
    {
        // Prefix with current time (minute) just to 'order' the IDs in the datastore which
        // can potentially aid indexing
        return ceil(time() / 60).'-'.RandUtils::randomStringFormat('%8An-%8An-%6An-%3A');
    }

    /**
     * @param string $str
     *
     * @return int
     */
    private function isValidFormat($str)
    {
        return preg_match('#^\d{8}\-[A-Z0-9]{8}\-[A-Z0-9]{8}\-[A-Z0-9]{6}\-[A-Z]{3}$#', $str);
    }

    /**
     * @return string
     */
    public function getVisitorIdentifier()
    {
        if ($this->request_stack->getMasterRequest()) {
            $identifier = $this->request_stack->getMasterRequest()->cookies->get(static::COOKIE_NAME);
            if ($identifier && $this->isValidFormat($identifier)) {
                $this->logger->info(sprintf('found visitor identifier in cookie "%s"', static::COOKIE_NAME));

                return $identifier;
            }
        }

        $identifier = static::generateRandomIdentifier();
        $this->logger->info(sprintf('no visitor identifier in request, created one: %s', $identifier));

        return $identifier;
    }
}
