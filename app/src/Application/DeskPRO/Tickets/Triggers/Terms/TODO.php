<?php
// Empty implementations of term objects as we build up crud

namespace Application\DeskPRO\Tickets\Triggers\Terms;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

class CheckMessage                             extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckHasAttach                           extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckHasAttachType                       extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckHasAttachName                       extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckUserEmailAddress                    extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckUserLabels                          extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckUserUsergroups                      extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckUserIsManager                       extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckPersonIsDisabled                    extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckOrgLabels                           extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckOrgUsergroups                       extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckDayOfWeek                           extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckTimeOfDay                           extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckWorkingHours                        extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }