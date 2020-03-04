<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Email;

use Application\DeskPRO\Entity\EmailSource;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\Query;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CloudEmailTestController.
 *
 * @ApiModes({"master_key", "key"})
 * @ApiUserContext("open")
 * @Rest\Route("/cloud-email-test")
 */
class CloudEmailTestController extends BaseController
{
    /**
     * @Rest\Post("/send")
     *
     * @param Request $request
     *
     * @return View
     */
    public function sendOutgoingTestEmail(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['subject']) || !isset($data['to'])) {
            return View::create([
                'message' => 'Body must contain "subject" and "to" properties',
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var \Application\EmailBundle\SwiftMailer\Message\Message $message */
        $message = $this->container->getMailer()->createMessage();
        $message->setTo($data['to']);
        $message->setSubject($data['subject']);
        $message->setBody('TEST EMAIL');

        $this->container->getMailer()->queueMessage($message);

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Get("/incoming/{subject}/check")
     *
     * @param string $subject
     *
     * @return View
     */
    public function checkIncomingEmail($subject)
    {
        $subject = base64_decode($subject);

        /** @var Connection $conn */
        $conn = $this->container->getEm()->getConnection();

        try {
            $idThresholdOffset = 500;

            $sourceLastId = $conn
                ->executeQuery('SELECT es.id FROM email_sources es ORDER BY es.id DESC LIMIT 1')
                ->fetchColumn(0)
            ;

            $idThreshold = $sourceLastId >= $idThresholdOffset
                ? $sourceLastId - $idThresholdOffset
                : 0
            ;

            $sourceCount = $conn
                ->executeQuery(
                    'SELECT COUNT(*) FROM email_sources es WHERE es.header_subject = :subject AND es.id > :idThreshold',
                    ['subject' => $subject, 'idThreshold' => $idThreshold]
                )
                ->fetchColumn(0)
            ;

            $ticketLastId = $conn
                ->executeQuery('SELECT t.id FROM tickets t ORDER BY t.id DESC LIMIT 1')
                ->fetchColumn(0)
            ;

            $idThreshold = $ticketLastId >= $idThresholdOffset
                ? $ticketLastId - $idThresholdOffset
                : 0
            ;

            $ticketCount = $conn
                ->executeQuery(
                    'SELECT COUNT(*) FROM tickets t WHERE t.original_subject = :subject AND t.id > :idThreshold',
                    ['subject' => $subject, 'idThreshold' => $idThreshold]
                )
                ->fetchColumn(0)
            ;

            return View::create([
                'sourceExists' => ($sourceCount > 0),
                'ticketExists' => ($ticketCount > 0),
            ]);
        } catch (DBALException $e) {
            return View::create(
                ['message' => 'Failed to run checking queries'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @Rest\Delete("/incoming")
     *
     * @param  Request $request
     * @return View
     * @throws \Exception
     */
    public function purgeIncomingAction(Request $request)
    {
        $before = $this->getBeforeThresholdFromRequest($request);

        if (!$before) {
            return View::create(
                ['message' => 'before date parameter must be added to query string, e.g. ?before=2020-03-03'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $db = $this->get('database_connection');
        $deletedTicketStatusId = $this->getContainer()->getTicketStatuses()->getDeletedStatus()->getId();
        $purgerAgentId = $this->getAgentIdForPurge();

        foreach ($this->getEmailSourcesBefore($before) as $source) {
            $db->executeQuery(
                'UPDATE tickets SET status = :hidden, ticket_status_id = :deletedStatusId WHERE id = :id',
                [
                    'hidden'          => 'hidden',
                    'deletedStatusId' => $deletedTicketStatusId,
                    'id'              => $source['ticket_id'],
                ]
            );

            $db->executeQuery(
                'UPDATE tickets_search_active SET status = :hidden, ticket_status_id = :deletedStatusId WHERE id = :id',
                [
                    'hidden'          => 'hidden',
                    'deletedStatusId' => $deletedTicketStatusId,
                    'id'              => $source['ticket_id'],
                ]
            );

            $db->replace(
                'tickets_deleted',
                [
                    'ticket_id'     => $source['ticket_id'],
                    'by_person_id'  => $purgerAgentId,
                    'new_ticket_id' => 0,
                    'reason'        => 'Mass Purge Operation (Email Test Process)',
                    'date_created'  => date('Y-m-d H:i:s'),
                ]
            );

            $db->delete('email_sources', ['id' => $source['source_id']]);
        }

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Delete("/outgoing")
     *
     * @param  Request $request
     * @return View
     * @throws \Exception
     */
    public function purgeOutgoingAction(Request $request)
    {
        $before = $this->getBeforeThresholdFromRequest($request);

        if (!$before) {
            return View::create(
                ['message' => 'before date parameter must be added to query string, e.g. ?before=2020-03-03'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $db = $this->get('database_connection');

        $db->executeQuery('DELETE FROM sendmail_sources WHERE date_created < :before', [
            'before' => $before->format('Y-m-d'),
        ]);

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param  Request $request
     * @return \DateTime|null
     * @throws \Exception
     */
    private function getBeforeThresholdFromRequest(Request $request)
    {
        if (!$request->query->has('before')) {
            return null;
        }

        return new \DateTime($request->query->get('before'));
    }

    /**
     * @param \DateTime $before
     * @return array
     */
    private function getEmailSourcesBefore(\DateTime $before)
    {
        $builder = $this->getManager()->createQueryBuilder();

        $query = $builder
            ->select('s.id AS source_id, s.object_id AS ticket_id')
            ->from(EmailSource::class, 's')
            ->andWhere('s.date_created < :before')
            ->andWhere('s.object_type = :objectType')
            ->setParameter('before', $before)
            ->setParameter('objectType', EmailSource::OBJ_TYPE_TICKET)
            ->getQuery()
        ;

        return $query->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * @return integer
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getAgentIdForPurge()
    {
        $builder = $this->getManager()->createQueryBuilder();

        $query = $builder
            ->select('p.id')
            ->from(Person::class, 'p')
            ->andWhere('p.is_agent = TRUE')
            ->andWhere('p.is_deleted = FALSE')
            ->setMaxResults(1)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
        ;

        return $query->getSingleScalarResult();
    }
}
