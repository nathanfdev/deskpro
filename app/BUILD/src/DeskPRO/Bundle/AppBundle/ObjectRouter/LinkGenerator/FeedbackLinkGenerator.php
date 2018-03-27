<?php

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
