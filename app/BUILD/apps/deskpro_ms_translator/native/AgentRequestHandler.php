<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_ms_translator;

use Application\DeskPRO\App\Native\RequestHandler\AgentRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\AgentRequestHandlerInterface;
use Application\DeskPRO\Entity\TicketMessageTranslated;

class AgentRequestHandler implements AgentRequestHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handleAgentRequest(AgentRequestContext $context)
    {
        switch ($context->getAction()) {
            case 'translate-ticket-message':
                return $this->translateMessageAction($context);

            case 'translate-text':
                return $this->translateTextAction($context);

            default:
                throw $context->createNotFoundException();
        }
    }

    /**
     * @param AgentRequestContext $context
     */
    private function translateMessageAction(AgentRequestContext $context)
    {
        $message_id = $context->getIn()->getUint('message_id');
        $from       = $context->getIn()->getString('from');
        $to         = $context->getIn()->getString('to');

        //------------------------------
        // Get ticket message
        //------------------------------

        /** @var \Application\DeskPRO\Entity\TicketMessage $message */
        $message = $context->getEm()->find('DeskPRO:TicketMessage', $message_id);

        if (!$message) {
            throw $context->createNotFoundException();
        }

        /** @var \Application\DeskPRO\Entity\Ticket $ticket */
        $ticket = $message->ticket;

        if (!$context->getAgent()->PermissionsManager->TicketChecker->canView($ticket)) {
            throw $context->createNotFoundException();
        }

        $message_text = $message->message;

        //------------------------------
        // Translate it
        //------------------------------

        $message_translated = $context->getEm()->getRepository('DeskPRO:TicketMessageTranslated')->getForMessage($message, $to);

        if ($message_translated) {
            // The translated text is only good if it matches the 'from' or if the user chose 'auto'
            if ($from == 'auto' || $from == $message_translated->from_lang_code) {
                return $context->createJsonResponse([
                    'ticket_id'             => $ticket->getId(),
                    'message_id'            => $message->getId(),
                    'message_translated_id' => $message_translated->getId(),
                    'message'               => $message_translated->message,
                    'from_lang_code'        => $message_translated->from_lang_code,
                    'to_lang_code'          => $message_translated->lang_code,
                ]);
            }
        }

        /** @var \Orb\Service\Microsoft\Translate\Translate $api */
        $api = $context->getAppService('ms_translator');

        if ($from == 'auto') {
            try {
                $from = $api->detect($message_text);
            } catch (\Exception $e) {
                return $context->createJsonResponse(['error_code' => 'no_detect', 'message' => 'Could not detect language', 'exception' => $e->getMessage()]);
            }

            if (!$message->lang_code) {
                $message->lang_code = $from;
                $context->getEm()->persist($message);
                $context->getEm()->flush();
            }
        }

        $message_translated = new TicketMessageTranslated();
        $message_translated->setTicketMessage($message);
        $message_translated->from_lang_code = $from;
        $message_translated->lang_code      = $to;

        try {
            $message_translated->message = $api->translate($message_text, $from, $to, 'text/html');
        } catch (\Exception $e) {
            return $context->createJsonResponse(['error_code' => 'no_translate', 'message' => 'Could not translate message', 'exception' => $e->getMessage()]);
        }

        $context->getEm()->persist($message_translated);
        $context->getEm()->flush();

        return $context->createJsonResponse([
            'ticket_id'             => $ticket->getId(),
            'message_id'            => $message->getId(),
            'message_translated_id' => $message_translated->getId(),
            'message'               => $message_translated->message,
            'from_lang_code'        => $message_translated->from_lang_code,
            'to_lang_code'          => $message_translated->lang_code,
        ]);
    }

    /**
     * @param AgentRequestContext $context
     */
    private function translateTextAction(AgentRequestContext $context)
    {
        $message_text = $context->getIn()->getRaw('message_text');
        $from         = $context->getIn()->getString('from');
        $to           = $context->getIn()->getString('to');

        /** @var \Orb\Service\Microsoft\Translate\Translate $api */
        $api = $context->getAppService('ms_translator');

        if ($from == 'me') {
            $from = $context->getAgent()->getLanguage()->getLocale();
        } elseif ($from == 'auto') {
            try {
                $from = $api->detect($message_text);
            } catch (\Exception $e) {
                return $context->createJsonResponse(['error_code' => 'no_detect', 'message' => 'Could not detect language', 'exception' => $e->getMessage()]);
            }
        }

        try {
            $trans_text = $api->translate($message_text, $from, $to, 'text/html');
        } catch (\Exception $e) {
            return $context->createJsonResponse(['error_code' => 'no_translate', 'message' => 'Could not translate message', 'exception' => $e->getMessage()]);
        }

        return $context->createJsonResponse([
            'message'        => $trans_text,
            'from_lang_code' => $from,
            'to_lang_code'   => $to,
        ]);
    }
}
