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
 * @subpackage
 */

namespace Application\AuthBundle\Voter\Portal;

use Application\AuthBundle\Voter\AbstractVoter;
use Application\DeskPRO\Entity\Ticket;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;

class TicketsVoter extends AbstractVoter
{
    const TICKET_LIST = 'TICKET_LIST';
    const TICKET_VIEW = 'TICKET_VIEW';
    const TICKET_EDIT = 'TICKET_EDIT';

    protected function getSupportedAttributes()
    {
        return array(self::TICKET_LIST, self::TICKET_VIEW, self::TICKET_EDIT);
    }

    protected function isGranted($attribute, $object, $user = null)
    {
        if (!$object instanceof Ticket) {
            throw new InvalidArgumentException('expected Ticket entity, but got "'. get_class($object) .'"');
        }

        if (!$this->isLoggedIn($user)) {
            return false;
        }

        switch($attribute) {
            case static::TICKET_LIST:
                return $this->isLoggedIn($user);

            case static::TICKET_VIEW:
            case static::TICKET_EDIT:
            return $object->person->getId() === $user->getId();
        }

        return false;
    }

    /**
     * Return an array of supported classes. This will be called by supportsClass
     *
     * @return array    an array of supported classes, i.e. array('Acme\DemoBundle\Model\Product')
     */
    protected function getSupportedClasses()
    {
        return array('Application\\DeskPRO\\Entity\\Ticket');
    }
}
 