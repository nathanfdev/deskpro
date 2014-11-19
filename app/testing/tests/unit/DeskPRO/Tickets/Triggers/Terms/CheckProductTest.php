<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckProductTest extends AbstractTicketEntityCheckTest
{
    /**
     * {@inheritDoc}
     */
    protected function getCheckClass()
    {
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckProduct';
    }

    /**
     * {@inheritDoc}
     */
    protected function getCheckClassOptionKey()
    {
        return 'product_ids';
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityClass()
    {
        return 'Application\\DeskPRO\\Entity\\Product';
    }

    /**
     * {@inheritDoc}
     */
    public function getTicketPropertyName()
    {
        return 'product';
    }
}
