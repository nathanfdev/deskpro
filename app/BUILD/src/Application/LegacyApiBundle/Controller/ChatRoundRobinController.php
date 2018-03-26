<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\ChatRoundRobin;
use Application\DeskPRO\Entity\ChatRoundRobinLogEntry;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use Application\LegacyApiBundle\PermissionStrategy\UserTypePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @ApiModes("all")
 */
class ChatRoundRobinController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new UserTypePermission(UserTypePermission::AGENT);
    }

    //###################################################################################################################
    // list
    //###################################################################################################################

    public function listAction()
    {
        $data = [];

        /** @var PersonRepository $personRepository */
        $personRepository = $this->em->getRepository(Person::class);

        /** @var $rr ChatRoundRobin */
        foreach ($this->em->getRepository(ChatRoundRobin::class)->findAll() as $rr) {
            $rrdata = $rr->toApiData();
            if ($next = $rr->getNextAgent($personRepository)) {
                $rrdata['next'] = $next->toApiData();
            }
            $data[] = $rrdata;
        }

        return $this->createApiResponse($data);
    }

    //##################################################################################################################
    // get RR
    //###################################################################################################################

    public function getAction($id)
    {
        /** @var $rr ChatRoundRobin */
        if (!$rr = $this->em->getRepository(ChatRoundRobin::class)->find($id)) {
            throw $this->createNotFoundException();
        }

        /** @var PersonRepository $personRepository */
        $personRepository = $this->em->getRepository(Person::class);

        $data = $rr->toApiData();
        if ($next = $rr->getNextAgent($personRepository)) {
            $data['next'] = $next->toApiData();
        }

        return $this->createApiResponse($data);
    }

    //##################################################################################################################
    // save RR
    //###################################################################################################################

    public function setAction($id)
    {
        /** @var \Application\DeskPRO\EntityRepository\ChatRoundRobin $rep */
        $rep = $this->em->getRepository(ChatRoundRobin::class);

        /* @var $rr ChatRoundRobin */
        if (!$id) {
            $rr = new ChatRoundRobin();
            $this->em->persist($rr);
        } elseif (!$rr = $rep->find($id)) {
            throw $this->createNotFoundException();
        }

        $data = $this->in->getAll('req');
        unset($data['next']);

        $agents      = $data['agents'];
        $departments = $data['departments'];

        unset($data['agents']);
        unset($data['departments']);

        $rr->fromArray($data);
        $rr->agents->clear();
        $rr->departments->clear();

        $rep->setDepartments($rr, $departments);
        $rep->setAgents($rr, $agents);

        $this->em->flush();

        return $this->getAction($rr['id']);
    }

    //##################################################################################################################
    // delete RR
    //###################################################################################################################

    public function deleteAction($id)
    {
        /** @var $rr ChatRoundRobin */
        if (!$rr = $this->em->getRepository(ChatRoundRobin::class)->find($id)) {
            throw $this->createNotFoundException();
        }

        $this->em->remove($rr);
        $this->em->flush();

        return $this->createApiResponse([]);
    }

    //##################################################################################################################
    // setup RR
    //###################################################################################################################

    public function settingsAction()
    {
        if ($this->request->isMethod('PUT')) {
            $enabled = $this->in->getBool('enabled');
            $this->settings->setSetting('core.chat_round_robin.enabled', $enabled);
        }

        return $this->createApiResponse([
            'enabled' => (bool) $this->settings->get('core.chat_round_robin.enabled', false),
        ]);
    }

    /**
     * @param $id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function logsAction($id)
    {
        if (!$rr = $this->em->find(ChatRoundRobin::class, $id)) {
            throw new NotFoundHttpException();
        }

        $entries = $this->em->getRepository(ChatRoundRobinLogEntry::class)->findBy(
            ['rr' => $rr],
            ['created' => 'desc']
        );

        return $this->render('AdminInterfaceBundle:ChatRoundRobin:logs.html.twig', [
            'entries' => $entries,
            'rr'      => $rr,
        ]);
    }
}
