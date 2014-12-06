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

namespace Application\FormBundle\Form\Type;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\TicketAttachment;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TicketMessageAttachmentCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event){
            $form = $event->getForm();
            $collection = $event->getData();

            // ensure there is a collection of attachments on the message (even if empty)
            if (!$collection instanceof Collection) {
                $collection = new ArrayCollection();
                $event->setData($collection);
            }

            // adds a new attachment (allowing the form to show one empty)
            $attachment = new TicketAttachment();
            $attachment->setMessage($form->getConfig()->getOption('ticket_message'));
            $attachment->person = $form->getConfig()->getOption('person');
            $collection->add($attachment);
        }, 100);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $collection = $event->getData();

            // clean up attachments that don't have a blob (delete them from the message)
            foreach ($collection as $attachment) {
                if (!$attachment->getBlob()) {
                    $collection->removeElement($attachment);
                }
            }

        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'type' => 'ticket_message_attachment',
                'options' => function (Options $options) {
                        return array(
                            'ticket_message' =>  $options->get('ticket_message'),
                            'person'         =>  $options->get('person')
                        );
                    },
                'allow_add' => true,
                'allow_delete' => true
            )
        );

        $resolver->setRequired(
            array(
                'ticket_message',
                'person'
            )
        );

        $resolver->setAllowedTypes(
            array(
                'ticket_message' => 'Application\\DeskPRO\\Entity\\TicketMessage',
                'person' => 'Application\\DeskPRO\\Entity\\Person'
            )
        );
    }

    public function getParent()
    {
        return 'collection';
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'ticket_message_attachment_collection';
    }
}
 