<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace Application\DeskPRO\Form\Type;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Task;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType implements EventSubscriberInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
            ])
            ->add('person', EntityType::class, [
                'class'         => Person::class,
                'required'      => true,
                'property'      => 'display_name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')->where('p.is_agent = true AND p.is_deleted = false');
                },
            ])
            // UTC!
            ->add('date_due', DateTimeType::class, [
                'widget'   => 'single_text',
                'required' => false,
            ])
            ->add('visibility', ChoiceType::class, [
                'required' => false,
                'choices'  => [
                    Task::PRIVATE_VISIBILITY => 'private',
                    Task::PUBLIC_VISIBILITY  => 'public',
                ],
                'empty_data'  => null,
                'empty_value' => Task::PUBLIC_VISIBILITY,
            ])
            ->add('assigned_agent', EntityType::class, [
                'class'         => Person::class,
                'required'      => false,
                'property'      => 'display_name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')->where('p.is_agent = true AND p.is_deleted = false');
                },
            ])
            ->add('assigned_agent_team', EntityType::class, [
                'class'    => AgentTeam::class,
                'required' => false,
                'property' => 'name',
            ])
            ->add('ticket', NumberType::class, [
                'required' => false,
                'mapped'   => false,
            ]);

        $builder->addEventSubscriber($this);
    }

    /**
     * just moved some code from controller.
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (empty($data['assigned_agent'])) {
            return;
        }

        if (false === strpos($data['assigned_agent'], ':')) {
            return;
        }

        list($type, $id)        = explode(':', $data['assigned_agent']);
        $data['assigned_agent'] = null;
        'agent' === $type
            ? $data['assigned_agent']      = $id
            : $data['assigned_agent_team'] = $id;

        $event->setData($data);
    }

    /**
     * additional associations.
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        if ($ticket_id = $form->get('ticket')->getData()) {
            $ticket = App::getOrm()->getRepository('DeskPRO:Ticket')->find($ticket_id);
            if ($ticket) {
                $assoc         = new \Application\DeskPRO\Entity\TaskAssociatedTicket();
                $task          = $form->getData();
                $assoc->ticket = $ticket;
                $assoc->task   = $task;
                $task->task_associations->add($assoc);
            }
        }

        // hardcoded date override
        $timezone = $form->getConfig()->getOption('timezone');
        if ($date = $form->get('date_due')->getData()) {
            /** @var $person Person */
            if ((!$person = $form->get('person')->getData()) && !$timezone) {
                return;
            }

            $date = new \DateTime($date->format('Y-m-d H:i:s'), $timezone ? new \DateTimeZone($timezone) : $person->getDateTimezone());
            $date->setTimezone(new \DateTimeZone('UTC'));
            $task             = $event->getForm()->getData();
            $task['date_due'] = $date;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'                    => Task::class,
                'timezone'                      => null,
                'csrf_protection'               => false,
                'csrf_double_submit_protection' => false,
            ])
            ->setRequired(['person'])
            ->setAllowedTypes('person', Person::class)
        ;
    }

    public function getName()
    {
        return 'task';
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT  => 'onPreSubmit',
            FormEvents::POST_SUBMIT => 'onPostSubmit',
        ];
    }
}
