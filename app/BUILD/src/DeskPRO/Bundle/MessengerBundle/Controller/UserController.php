<?php

namespace DeskPRO\Bundle\MessengerBundle\Controller;

use Application\DeskPRO\Entity\ChatConversation;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Entity\ActionAlert;
use DeskPRO\Bundle\MessengerBundle\Security\Authentication\MessengerAuthenticator;
use DeskPRO\Bundle\MessengerBundle\Serializer\Model\UserInfo;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserController.
 *
 * @ApiModes("all")
 * @ApiUserContext("open")
 *
 * @Rest\Route("/user")
 */
class UserController extends BaseController
{
    /**
     * You can use this endpoint to gather information about clients you need to obtain notifications and alerts.
     *
     * @ApiDoc(
     *     section="Messenger",
     *     resourceDescription="Testing new Bundle and Kernel",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     requirements={
     *          {
     *              "name"="visitorId",
     *              "requirement"="[a-zA-Z0-9\\.\\-_]+",
     *              "description"="id of the visitor to look for",
     *              "dataType"="string"
     *          }
     *      }
     * )
     * @Rest\Get("")
     *
     * @param Request $request
     *
     * @throws NonUniqueResultException
     *
     * @return View
     */
    public function loadUserInfoAction(Request $request)
    {
        $visitorId = $request->headers->get(MessengerAuthenticator::VISITOR_HEADER_NAME);

        /** @var EntityManager $em */
        $em                   = $this->get('doctrine.orm.default_entity_manager');
        $chatConversationRepo = $em->getRepository(ChatConversation::class);
        $chats                = $chatConversationRepo->findBy(['visitor_id' => $visitorId], ['date_created' => 'DESC'], 25);

        $actionAlertRepo = $em->getRepository(ActionAlert::class);
        $qb              = $actionAlertRepo->createQueryBuilder('aa');
        $alert           = $qb->where('aa.target_id = :visitorId')
            ->orderBy('aa.id', 'DESC')
            ->setParameter('visitorId', $visitorId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $alert = $alert ? $alert->getId() : 0;

        $userInfo = new UserInfo($visitorId, $alert);

        return View::create($this->wrap($userInfo->addChats($chats)), Response::HTTP_OK);
    }

    /**
     * You can use this endpoint to fetch latest action alerts.
     *
     * @ApiDoc(
     *     section="Messenger",
     *     resourceDescription="Gathering action_alerts",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     requirements={
     *          {
     *              "name"="lastActionAlert",
     *              "requirement"="\d+",
     *              "description"="id of last action alert",
     *              "dataType"="integer"
     *          }
     *      }
     * )
     * @Rest\Get("/action_alerts/{lastActionAlert}")
     *
     * @param int     $lastActionAlert
     * @param Request $request
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return View
     */
    public function loadLastActionAlerts($lastActionAlert, Request $request)
    {
        $visitorId = $request->headers->get(MessengerAuthenticator::VISITOR_HEADER_NAME);

        $connection = $this->get('doctrine.dbal.read_connection');

        $sql = <<<'SQL'
SELECT * FROM `notify_action_alerts`
WHERE (`target_id` = :target_id)
  AND `id` > :last
ORDER BY `id` ASC
SQL;
        $stmnt = $connection->prepare($sql);
        $stmnt->execute([
            'target_id' => $visitorId,
            'last'      => $lastActionAlert,
        ]);

        $all = $stmnt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($all as &$datum) {
            foreach ($datum as &$innerData) {
                if (is_numeric($innerData)) {
                    $innerData = (int) $innerData;
                }
            }
            $date                  = new \DateTime($datum['date_created']);
            $datum['date_created'] = $date->format(\DateTime::ISO8601);
            $datum['timestamp']    = $date->getTimestamp();
            if (isset($datum['data'])) {
                $datum['data'] = @json_decode($datum['data'], true);
            }
        }

        return View::create($all, Response::HTTP_OK);
    }
}
