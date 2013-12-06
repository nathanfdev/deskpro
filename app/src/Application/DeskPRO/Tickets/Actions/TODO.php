<?php
// Empty implementations of term objects as we build up crud

namespace Application\DeskPRO\Tickets\Actions;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Tickets\ExecutorContext;

class SetSlas                                  extends AbstractAction { public function applyAction(Ticket $ticket, ExecutorContext $context) { } public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContext $context) { return array(); } public function applyMacro(Person $person, Ticket $ticket, ExecutorContext $context) {} }
class SetSlaStatus                             extends AbstractAction { public function applyAction(Ticket $ticket, ExecutorContext $context) { } public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContext $context) { return array(); } public function applyMacro(Person $person, Ticket $ticket, ExecutorContext $context) {} }
class SetSlaRequirements                       extends AbstractAction { public function applyAction(Ticket $ticket, ExecutorContext $context) { } public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContext $context) { return array(); } public function applyMacro(Person $person, Ticket $ticket, ExecutorContext $context) {} }
class ChangeUser                               extends AbstractAction { public function applyAction(Ticket $ticket, ExecutorContext $context) { } public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContext $context) { return array(); } public function applyMacro(Person $person, Ticket $ticket, ExecutorContext $context) {} }
class DeleteTicket                             extends AbstractAction { public function applyAction(Ticket $ticket, ExecutorContext $context) { } public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContext $context) { return array(); } public function applyMacro(Person $person, Ticket $ticket, ExecutorContext $context) {} }
class AddAgentReply                            extends AbstractAction { public function applyAction(Ticket $ticket, ExecutorContext $context) { } public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContext $context) { return array(); } public function applyMacro(Person $person, Ticket $ticket, ExecutorContext $context) {} }
