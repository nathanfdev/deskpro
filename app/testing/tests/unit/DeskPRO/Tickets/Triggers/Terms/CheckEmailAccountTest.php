<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckEmailAccountTest extends AbstractTicketEntityCheckTest
{
    /**
     * {@inheritDoc}
     */
    protected function getCheckClass()
    {
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckEmailAccount';
    }

    /**
     * {@inheritDoc}
     */
    protected function getCheckClassOptionKey()
    {
        return 'email_account_ids';
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityClass()
    {
        return 'Application\\DeskPRO\\Entity\\EmailAccount';
    }

    /**
     * {@inheritDoc}
     */
    public function getTicketPropertyName()
    {
        return 'email_account';
    }

    /**
     * {@inheritDoc}
     */
    public function createEntityObject($id)
    {
        $ent_class = $this->getEntityClass();

        $object = new $ent_class('tickets');
        $object->id = $id;

        return $object;
    }
}
