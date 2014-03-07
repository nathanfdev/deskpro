<?php
// Empty implementations of term objects as we build up crud

namespace Application\DeskPRO\Tickets\Triggers\Terms;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

class CheckEmailAccount                        extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckEmailSubject                        extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckEmailBody                           extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckEmailToName                         extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckEmailToAddress                      extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckEmailFromName                       extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckEmailFromAddress                    extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckEmailCcName                         extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckEmailCcAddress                      extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckEmailHeader                         extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }

class CheckMessage                             extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckHasAttach                           extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckHasAttachType                       extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckHasAttachName                       extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckUserEmailAddress                    extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckUserLabels                          extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckUserUsergroups                      extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckUserLanguage                        extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckUserIsManager                       extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckPersonIsDisabled                    extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckOrgLabels                           extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckOrgEmailDomain                      extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckOrgUsergroups                       extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckDayOfWeek                           extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckTimeOfDay                           extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }
class CheckWorkingHours                        extends AbstractTriggerTerm { public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $ticket_changelog) { return true; } }