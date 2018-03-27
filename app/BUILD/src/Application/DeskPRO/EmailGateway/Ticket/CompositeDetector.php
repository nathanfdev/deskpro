<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Ticket;

use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\Entity\Ticket;
use Orb\Log\Logger;
use Orb\Util\Util;

class CompositeDetector implements TicketDetectorInterface, BounceAwareInterface, TacPersonDetectorInterface, PublicTacAware
{
    /**
     * @var \Application\DeskPRO\EmailGateway\Ticket\TicketDetectorInterface[]
     */
    private $detectors = [];

    /**
     * @var bool
     */
    private $is_bounce_mode = false;

    /**
     * @var \Application\DeskPRO\EmailGateway\Ticket\TicketDetectorInterface[]
     */
    private $matched_detectors = [];

    /**
     * @var \Application\DeskPRO\Entity\Ticket[]
     */
    private $matched_tickets = [];

    /**
     * @var \Orb\Log\Logger
     */
    private $logger;

    /**
     * @var bool
     */
    protected $publicTac = false;

    /**
     * @param TicketDetectorInterface $detector
     */
    public function addDetector(TicketDetectorInterface $detector)
    {
        $this->detectors[] = $detector;
        if (method_exists($detector, 'setLogger')) {
            $detector->setLogger($this->getLogger());
        }
    }

    /**
     * Runs detectors against a reader.
     *
     * @param AbstractReader $reader
     */
    private function runDetectors(AbstractReader $reader)
    {
        $reader_id = spl_object_hash($reader);

        foreach ($this->detectors as $detector) {
            if ($this->is_bounce_mode && $detector instanceof BounceAwareInterface) {
                $detector->enableBouncedMode();
            }

            $t = $detector->findExistingTicket($reader);
            if ($t) {
                $this->matched_detectors[$reader_id] = $detector;
                $this->matched_tickets[$reader_id]   = $t;
                $this->getLogger()->logInfo(sprintf('[CompositeDetector] %s: Found Ticket #%d', Util::getBaseClassname($detector), $t->id));

                return;
            } else {
                $this->getLogger()->logInfo(sprintf('[CompositeDetector] %s: No match', Util::getBaseClassname($detector)));
            }
        }

        $this->matched_detectors[$reader_id] = false;
        $this->matched_tickets[$reader_id]   = false;
    }

    /**
     * Reset the saved detector state.
     */
    public function reset()
    {
        $this->matched_detectors = [];
        $this->matched_tickets   = [];
        $this->is_bounce_mode    = false;
    }

    /**
     * @param AbstractReader $reader
     *
     * @return \Application\DeskPRO\EmailGateway\Ticket\TicketDetectorInterface|null
     */
    public function getMatchedDetector(AbstractReader $reader)
    {
        $reader_id = spl_object_hash($reader);
        if (!isset($this->matched_detectors[$reader_id])) {
            $this->runDetectors($reader);
        }

        if ($this->matched_detectors[$reader_id] === false) {
            return;
        }

        return $this->matched_detectors[$reader_id];
    }

    /**
     * {@inheritdoc}
     */
    public function enableBouncedMode()
    {
        $this->is_bounce_mode = true;
    }

    /**
     * {@inheritdoc}
     */
    public function findExistingTicket(AbstractReader $reader)
    {
        $this->publicTac = false;
        $detector        = $this->getMatchedDetector($reader);

        $reader_id = spl_object_hash($reader);
        if ($this->matched_tickets[$reader_id] === false) {
            return;
        }

        if ($detector instanceof PublicTacAware) {
            $this->publicTac = $detector->isPublicTac();
        }

        return $this->matched_tickets[$reader_id];
    }

    /**
     * {@inheritdoc}
     */
    public function findExistingPerson(Ticket $ticket, AbstractReader $reader)
    {
        $detector = $this->getMatchedDetector($reader);

        if (!$detector) {
            return;
        }

        return $detector->findExistingPerson($ticket, $reader);
    }

    /**
     * {@inheritdoc}
     */
    public function canAddUnknownPerson(Ticket $ticket, AbstractReader $reader)
    {
        $detector = $this->getMatchedDetector($reader);
        if (!$detector) {
            return false;
        }

        return $detector->canAddUnknownPerson($ticket, $reader);
    }

    /**
     * {@inheritdoc}
     */
    public function findTacPerson(AbstractReader $reader)
    {
        $detector = $this->getMatchedDetector($reader);
        if (!$detector || !($detector instanceof TacPersonDetectorInterface)) {
            return;
        }

        return $detector->findTacPerson($reader);
    }

    /**
     * Set the logger.
     *
     * @param \Orb\Log\Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \Orb\Log\Logger
     */
    public function getLogger()
    {
        if (!$this->logger) {
            $this->logger = new Logger();
        }

        return $this->logger;
    }

    public function isPublicTac()
    {
        return $this->publicTac;
    }
}
