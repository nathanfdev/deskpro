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

namespace DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGenerator;

use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\FeedbackStatusCategory;
use DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGeneratorInterface;
use DeskPRO\Bundle\PortalBundle\Helper\FeedbackFilterUriHelper;
use DeskPRO\Bundle\PortalBundle\Model\FeedbackFilter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Creates the proper link for a download "serve" (a direct url to put in an img tag for ex.)
 * This is because the normal "save" type will first hit a controller
 * for security and to increment count + redirect
 * this avoids both security and the download increment, so be careful with the "serve" type on Downloads!
 */
class FeedbackLinkGenerator implements LinkGeneratorInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $url_generator;

    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface $url_generator
     */
    public function __construct(UrlGeneratorInterface $url_generator)
    {
        $this->url_generator = $url_generator;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, $type, $context)
    {
        return $object instanceof FeedbackCategory || $object instanceof FeedbackStatusCategory;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($object, $type, $context, $extra_params, $reference_type)
    {
        $filter = new FeedbackFilter();

        if ($object instanceof FeedbackCategory) {
            $filter->setTypes([$object->getId()]);
        } elseif ($object instanceof FeedbackStatusCategory) {
            $filter->setStatus($object->getStatusType());
            $filter->setStatusCategories([$object->getId()]);
        }

        $uri_helper = new FeedbackFilterUriHelper();
        $filter_uri = $uri_helper->generateUriSegment($filter);

        return $this->url_generator->generate(
            'portal_feedback_browse',
            array_merge([
                'filter_uri' => $filter_uri,
            ], $extra_params),
            $reference_type
        );
    }
}
