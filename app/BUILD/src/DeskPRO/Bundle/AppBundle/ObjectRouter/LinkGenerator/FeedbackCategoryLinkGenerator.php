<?php

namespace DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGenerator;

use Application\DeskPRO\Entity\CommunityChannel;
use Application\DeskPRO\Entity\CommunityTopicStatusCategory;
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
class FeedbackCategoryLinkGenerator implements LinkGeneratorInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, $type, $context)
    {
        return $object instanceof CommunityChannel || $object instanceof CommunityTopicStatusCategory;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($object, $type, $context, $extra_params, $reference_type)
    {
        $filter = new FeedbackFilter();

        if ($object instanceof CommunityChannel) {
            $filter->setTypes([$object->getId()]);
        } elseif ($object instanceof CommunityTopicStatusCategory) {
            $filter->setStatus($object->getStatusType());
            $filter->setStatusCategories([$object->getId()]);
        }

        $uri_helper = new FeedbackFilterUriHelper();
        $filter_uri = $uri_helper->generateUriSegment($filter);

        return $this->urlGenerator->generate(
            'portal_community_browse',
            array_merge([
                'filter_uri' => $filter_uri,
                'brand'      => $object->getBrand(),
            ], $extra_params),
            $reference_type
        );
    }
}
