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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\AgentChat;

use Proxies\__CG__\DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class MarkMessageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'api_agent_chat_mark_message';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'ids',
                'collection',
                [
                    'entry_type'    => 'integer',
                    'entry_options' => [
                        'constraints' => [
                            new Assert\GreaterThan(['value' => 0]),
                        ],
                    ],
                    'allow_add' => true,
                ]
            )
            ->add('status', 'choice', [
                'choices' => [
                        'New'       => AgentChatMessage::STATUS_NEW,
                        'Delivered' => AgentChatMessage::STATUS_DELIVERED,
                        'Read'      => AgentChatMessage::STATUS_READ,
                    ],
                'choices_as_values' => true,
                'required'          => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection'               => false,
            'csrf_double_submit_protection' => false,
        ]);
    }
}
