<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\Portal;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Session;
use DeskPRO\Bundle\AppBundle\Security\Voter\AbstractVoter;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class PortalDesignerVoter.
 */
class PortalDesignerVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof Request && (
            strpos($subject->getPathInfo(), '/portal/api/style') === 0
            || strpos($subject->getPathInfo(), '/portal/api/emails') === 0);
    }

    /**
     * {@inheritdoc}
     *
     * @param Request $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        // not logged in to portal
        foreach (['dpsid-agent', 'dpsid-admin'] as $cookie) {
            if ($sid = $subject->cookies->get($cookie)) {
                if (strpos($sid, '-') === false) {
                    continue;
                }

                $sessionId    = Session::getIdFromCode($sid);
                $agentSession = $em->getConnection()->fetchAssoc(
                    '
                    SELECT person_id, auth
                    FROM sessions
                    WHERE id = ?
                ',
                    [
                        $sessionId,
                    ]
                );

                list(, $auth) = explode('-', $sid);
                if ($agentSession && $agentSession['auth'] === $auth && $agentSession['person_id']) {
                    /** @var Person $person */
                    $person = $em->getRepository(Person::class)->find($agentSession['person_id']);
                    if ($person->canAdmin()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
