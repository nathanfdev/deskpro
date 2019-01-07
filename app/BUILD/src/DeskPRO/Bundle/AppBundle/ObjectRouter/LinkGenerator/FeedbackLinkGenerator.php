<?php

namespace DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGenerator;

use Application\DeskPRO\Entity\Feedback;
use DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Creates the proper link for a feedback "serve" (a direct url to put in an img tag for ex.).
 */
class FeedbackLinkGenerator implements LinkGeneratorInterface
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
        return $object instanceof Feedback;
    }

    /**
     * {@inheritdoc}
     *
     * @param Feedback $object
     */
    public function generate($object, $type, $context, $extra_params, $reference_type)
    {
        switch ($type) {
            case 'vote_up':
                $route = 'portal_feedback_vote_up';
                break;
            case 'vote_down':
                $route = 'portal_feedback_vote_down';
                break;
            case 'toggle_subscription':
                $route = 'portal_feedback_toggle_subscription';
                break;
            default:
                $route = 'portal_feedback_view';
        }

        return $this->urlGenerator->generate(
            $route,
            array_merge([
                'slug'  => $object->getSlug(),
                'brand' => $object->getBrand(),
            ], $extra_params),
            $reference_type
        );
    }
}
