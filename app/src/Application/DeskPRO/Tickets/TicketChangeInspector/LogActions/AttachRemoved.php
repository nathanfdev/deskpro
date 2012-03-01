<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets\TicketChangeInspector\LogActions;

class AttachRemoved implements LogActionInterface
{
	/**
	 * @var \Application\DeskPRO\Entity\TicketAttachment
	 */
	protected $attach;

	/**
	 * @var int
	 */
	protected $old_id;

	/**
	 * @var int
	 */
	protected $old_blob_id;

	public function __construct($attach)
	{
		$this->attach = $attach;

		// We need to copy the IDs now because after
		// the records have been removed, Doctrine sets the IDs to 0
		$this->old_id = $attach->id;
		$this->old_blob_id = $attach->blob->id;
	}

	public function getLogName()
	{
		return 'attach_added';
	}

	public function getLogDetails()
	{
		$details = array();
		$details['id_before']     = $this->old_id;
		$details['old_attach_id'] = $this->old_id;
		$details['blob_id']       = $this->attach->blob->id;
		$details['filename']      = $this->attach->blob->filename;
		$details['filesize']      = $this->attach->blob->filesize;

		return $details;
	}

	public function getEventType()
	{
		return 'property';
	}
}
