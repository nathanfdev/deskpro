<?php

namespace Application\DeskPRO\NewSearch\Manager;

use Application\DeskPRO\Entity\Ticket;
use Doctrine\ORM\EntityRepository;
use League\Url\Url;

/**
 * Trait SearchesTicketByUrlTrait
 * @package Application\DeskPRO\NewSearch\Manager
 */
trait SearchesTicketByUrlTrait
{
    /**
     * @param string           $q
     * @param EntityRepository $repository
     * @return array
     */
    protected function searchTicketByUrl($q, EntityRepository $repository)
    {
        // stub
        $tickets = [];

        // Try to parse an URL, in case it's provided as a search query
        try {
            // all cases: we do not care about the host, we just need to check if could get ticket number
            // so just attempt to parse URL. Unfortunately, the old League\Url is not smart enough and
            // can't validate the URL, so just catch \RuntimeException in case of parse failure.
            $url = Url::createFromUrl($q);

            // case 1: searh by ticket number in framgment
            // eg.: https://support.deskpro.com/agent/#app.tickets,inbox:agent,t:109346,t:108966,t:108169,p.o:65264,vis:7
            $fragment = $url->getFragment()->get();
            if (!empty($fragment)) {
                preg_match_all('/[a-zA-Z]\.o:(\d*)/', $fragment, $matches);
                foreach (array_unique(array_filter($matches[1])) as $ticketId) {
                    $ticket = $repository->findTicketId((int) $ticketId);
                    if ($ticket instanceof Ticket &&
                        $this->person->PermissionsManager->TicketChecker->canView($ticket))
                    {
                        $tickets[] = $ticket;
                    }
                }
            }

            // case 2: search ticket number in path,
            // eg.: https://support.deskpro.com/agent/go/ticket/109346
            $path = $url->getPath()->toArray();
            $index = array_search('ticket', $path);
            if (is_int($index) && array_key_exists($index + 1, $path)) {
                $ticket = $repository->findTicketId((int)$path[$index + 1]);
                if ($ticket instanceof Ticket &&
                    $this->person->PermissionsManager->TicketChecker->canView($ticket))
                {
                    $tickets[] = $ticket;
                }
            }
        } catch (\RuntimeException $exception) {
            // do nothing, and just let it go further
        }

        return $tickets;
    }
}
