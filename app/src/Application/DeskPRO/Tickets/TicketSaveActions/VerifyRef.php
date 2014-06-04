<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\RefGenerator\RefGeneratorInterface;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Kernel\KernelErrorHandler;
use Orb\Util\Strings;

class VerifyRef implements TicketSaveActionInterface
{
	/**
	 * @var RefGeneratorInterface
	 */
	private $ref_generator;


	/**
	 * @param RefGeneratorInterface $ref_generator
	 */
	public function __construct(RefGeneratorInterface $ref_generator)
	{
		$this->ref_generator = $ref_generator;
	}
	

	/**
	 * @param Ticket                   $ticket
	 * @param ExecutorContextInterface $context
	 * @return void
	 */
	public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
	{
		if ($context->getEventType() == 'noop') {
			return;
		}

		if (!$ticket->ref || (isset($ticket->__dp_is_autogen_ref) && $ticket->__dp_is_autogen_ref)) {
			try {
				$ticket->ref = $this->ref_generator->generateReference('DeskPRO:Ticket');
			} catch (\Exception $e) {
				KernelErrorHandler::logException($e);

				$ref = Strings::random(4, Strings::CHARS_ALPHA_IU) . '-' . Strings::random(4, Strings::CHARS_NUM) . '-' . Strings::random(4, Strings::CHARS_ALPHA_IU) . '-' . date('ymd');
				$ticket->ref = $ref;
			}
		}
	}

}