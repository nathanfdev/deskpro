<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\ImportBundle\Generator\Writer\DeskPRO\Importer;

use Application\DeskPRO\TicketLayout\LayoutField;
use Application\ImportBundle\Entity;

/**
 * DeskPRO ticket layout importer.
 *
 * Class TicketCustomDefLayout
 */
final class TicketCustomDefLayout extends AbstractImporter
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_TICKET_CUSTOM_DEF;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(Entity\EntityInterface $entity, $entity_id = null)
    {
        $custom_def = $this->getTicketCustomDefMapper()->findOneBy(array('id' => $entity_id));
        $layouts    = $this->getTicketLayoutMapper()->findAll();

        foreach ($layouts as $layout) {
            $change = false;

            $user_layout  = clone $layout->user_layout;
            $agent_layout = clone $layout->agent_layout;

            if (!$user_layout->has($custom_def->getId())) {
                $field = new LayoutField('ticket_field', $custom_def->getId());
                $field->setOptionsFromArray(array(
                    'on_editticket'      => true,
                    'on_viewticket'      => true,
                    'on_viewticket_mode' => 'value',
                    'on_newticket'       => true,
                ));

                $user_layout->add($field, 'message');
                $change = true;
            }

            if (!$user_layout->has($custom_def->getId())) {
                $field = new LayoutField('ticket_field', $custom_def->getId());
                $field->setOptionsFromArray(array(
                    'on_editticket'      => true,
                    'on_viewticket'      => true,
                    'on_viewticket_mode' => 'value',
                    'on_newticket'       => true,
                ));

                $agent_layout->add($field, 'message');
                $change = true;
            }

            if ($change) {
                $layout->user_layout  = $user_layout;
                $layout->agent_layout = $agent_layout;
                $layout->date_updated = new \DateTime();

                $this->records->addRelatedEntity($layout);
            }
        }
    }

    /**
     * Returns the organization custom def mapper.
     *
     * @return Mapper\TicketLayout
     */
    protected function getTicketLayoutMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_TICKET_LAYOUT);
    }
}
