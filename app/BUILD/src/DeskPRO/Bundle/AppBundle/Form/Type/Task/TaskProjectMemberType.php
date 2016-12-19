<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Task;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\ProjectMember;
use DeskPRO\Bundle\AppBundle\Entity\TaskProject;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TaskProjectMemberType.
 */
class TaskProjectMemberType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('member', EntityType::class, [
            'property_path' => $options['type'],
            'class'         => $this->getMemberClass($options['type']),
        ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetProject']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['project', 'type'])
            ->setDefaults([
                'data_class' => ProjectMember::class,
            ])
            ->setAllowedTypes('project', TaskProject::class)
            ->setAllowedValues('type', ['person', 'team', 'department'])
        ;
    }

    /**
     * @param FormEvent $event
     */
    public function onSetProject(FormEvent $event)
    {
        /** @var ProjectMember $data */
        $data = $event->getData();
        $data->setProject($event->getForm()->getConfig()->getOption('project'));
    }

    /**
     * @param string $type
     *
     * @return mixed
     */
    protected function getMemberClass($type)
    {
        switch ($type) {
            case 'person':
                return Person::class;
            case 'team':
                return AgentTeam::class;
            case 'department':
                return Department::class;
        }

        return false;
    }
}
