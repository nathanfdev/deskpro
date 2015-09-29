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

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use DeskPRO\Bundle\AppBundle\Entity\TaskProject;
use DeskPRO\Bundle\AppBundle\Form\EventListener\ReplaceNotSubmittedValuesWithDefaultsListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProjectType extends AbstractType
{
    /**
     * @var TaskProject
     */
    private $project;

    /**
     * @return string
     */
    public function getName()
    {
        return 'project';
    }

    /**
     * Build the form.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->project = $options['project'];

        $builder->addEventSubscriber(new ReplaceNotSubmittedValuesWithDefaultsListener());
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSubmit']);
        $builder->add(
            'title',
            'text',
            array(
                'description' => 'the project title',
            )
        )
        ->add(
            'departments',
            'collection',
            array(
                'type'         => 'project_department',
                'allow_add'    => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'options'      => array(
                    'project'     => $options['project'],
                    'required'    => false,
                    'description' => 'project members which are departments',
                ),
            )
        )
        ->add(
            'teams',
            'collection',
            array(
                'type'         => 'project_agent_team',
                'allow_add'    => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'options'      => array(
                    'project'     => $options['project'],
                    'required'    => false,
                    'description' => 'project members which are teams',
                ),
            )
        )
        ->add(
            'agents',
            'collection',
            array(
                'type'         => 'project_person',
                'allow_add'    => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'options'      => array(
                    'project'     => $options['project'],
                    'required'    => false,
                    'description' => 'project members which are people',
                ),
            )
        );
    }

    /**
     * Set default options for the form.
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'     => 'DeskPRO\Bundle\AppBundle\Entity\TaskProject',
            'project'        => new TaskProject(),
            'entity_manager' => null,
        ));
    }

    /**
     * OnSubmit listener for the form
     * N.B. Accessed as callback, so must be public.
     *
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        /** @var TaskProject $data */
        $data       = $event->getData();
        $newMembers = $data->getMembers();

        foreach ($this->project->getMembers() as $member) {
            if (!$newMembers->contains($member)) {
                $this->project->removeMember($member);
            }
        }
    }
}
