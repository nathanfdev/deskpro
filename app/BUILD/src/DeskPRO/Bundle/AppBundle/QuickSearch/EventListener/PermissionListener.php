<?php

namespace DeskPRO\Bundle\AppBundle\QuickSearch\EventListener;

use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvent;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvents;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * Class PermissionListener.
 */
class PermissionListener implements EventSubscriberInterface
{
    /**
     * @var AuthorizationChecker
     */
    private $authorizationChecker;

    /**
     * Constructor.
     *
     * @param AuthorizationChecker $authorizationChecker
     */
    public function __construct(AuthorizationChecker $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            QuickSearchEvents::POST_SEARCH => 'onCheckPermissions',
        ];
    }

    /**
     * @param QuickSearchEvent $event
     */
    public function onCheckPermissions(QuickSearchEvent $event)
    {
        $context = $event->getContext();

        foreach ($context->getEntities() as $entity) {
            if (!$this->authorizationChecker->isGranted(PermissionGroupVoter::VIEW, new PermissionGroupContext($entity))) {
                $context->removeEntity($entity);
            }
        }
    }
}
