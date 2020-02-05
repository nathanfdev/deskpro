<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Email;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
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
}
