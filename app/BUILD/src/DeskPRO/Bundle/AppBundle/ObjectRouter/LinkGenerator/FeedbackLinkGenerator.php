<?php

namespace DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGenerator;

use Application\DeskPRO\Entity\CommunityTopic;
use DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGeneratorInterface;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Creates the proper link for a feedback "serve" (a direct url to put in an img tag for ex.).
 */
class FeedbackLinkGenerator implements LinkGeneratorInterface
{
    const TYPE_PERMALINK = 'permalink';

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
        return $object instanceof CommunityTopic;
    }

    /**
     * {@inheritdoc}
     *
     * @param CommunityTopic $object
     */
    public function generate($object, $type, $context, $extra_params, $reference_type)
    {
        switch ($type) {
            case 'vote_up':
                $route = 'portal_community_topic_vote_up';
                break;
            case 'vote_down':
                $route = 'portal_community_topic_vote_down';
                break;
            case 'toggle_subscription':
                $route = 'portal_community_topic_toggle_subscription';
                break;
            default:
                $route = "{$context}_community_topic_view";
        }

        // Use feedback_id instead slug for 'agent' context
        $routeParam = static::TYPE_PERMALINK === $type || ObjectRouter::CONTEXT_AGENT === $context
            ? $object->getId()
            : $object->getSlug();

        $routeKey = ObjectRouter::CONTEXT_AGENT === $context
            ? 'communityTopicId'
            : 'slug';

        return $this->urlGenerator->generate(
            $route,
            array_merge(
                [
                    $routeKey => $routeParam,
                    'brand'   => $object->getBrand(),
                ],
                $extra_params
            ),
            $reference_type
        );
    }
}
