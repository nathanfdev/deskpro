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

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use Application\DeskPRO\Entity\Session;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\PortalBundle\Annotation\Dpsid;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Uses to cross-domain widget authorization.
 *
 * Class DpsidListener.
 */
class DpsidListener implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param TokenStorage  $tokenStorage
     * @param Reader        $annotationReader
     */
    public function __construct(EntityManager $em, TokenStorage $tokenStorage, Reader $annotationReader)
    {
        $this->em           = $em;
        $this->tokenStorage = $tokenStorage;
        $this->reader       = $annotationReader;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => ['onController', 150],
        ];
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onController(FilterControllerEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (strpos($request->getPathInfo(), '/portal/api') !== 0) {
            return;
        }

        $controller = $event->getController();

        $className = ClassUtils::getClass($controller[0]);
        $object    = new \ReflectionClass($className);
        $method    = $object->getMethod($controller[1]);

        if (!$this->reader->getClassAnnotation($object, Dpsid::class)
            && !$this->reader->getMethodAnnotation($method, Dpsid::class)) {
            return;
        }

        if (!$token = $request->query->get('dpsid')) {
            throw new AccessDeniedHttpException('No token provided');
        }

        /** @var \Application\DeskPRO\EntityRepository\Session $repo */
        $repo = $this->em->getRepository(Session::class);

        if (!$session = $repo->getSessionFromCode($token)) {
            throw new AccessDeniedHttpException('Invalid token');
        } else {
            $this->tokenStorage->setToken(self::createTokenFromSession($session));
        }
    }

    /**
     * @param Session $session
     *
     * @return AnonymousToken|UsernamePasswordToken
     */
    public static function createTokenFromSession(Session $session)
    {
        $person = $session->getPerson() ?: new PersonGuest();

        return new UsernamePasswordToken($person, $session->getId(), 'portal_api', $person->getRoles());
    }
}
