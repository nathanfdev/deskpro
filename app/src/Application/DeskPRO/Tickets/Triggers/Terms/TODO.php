<?php
// Empty implementations of term objects as we build up crud

namespace Application\DeskPRO\Tickets\Triggers\Terms;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

class CheckDayOfWeek                           extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckTimeOfDay                           extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckWorkingHours                        extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }