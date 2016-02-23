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
namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Application\DeskPRO\Entity\Usergroup;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class UsergroupsType.
 */
class UsergroupsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        /* @var OptionsResolver $resolver */
        $resolver
            ->setRequired(['owner', 'is_agent_group'])
            ->setAllowedTypes([
                'is_agent_group' => 'bool',
            ])
            ->setDefaults([
                'class'         => Usergroup::class,
                'multiple'      => true,
                'query_builder' => function (EntityRepository $repository, $options) {
                    return $repository
                        ->createQueryBuilder('u')
                        ->where('u.is_agent_group = '.(int) $options['is_agent_group'])
                    ;
                },
            ])
            ->setNormalizer('query_builder', function (Options $options, $qb) {
                return call_user_func($qb, $options['em']->getRepository($options['class']), $options);
            })
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onFilterValues']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onMergeValues'], 100);
    }

    /**
     * @param FormEvent $event
     */
    public function onFilterValues(FormEvent $event)
    {
        $form       = $event->getForm();
        $all_groups = $event->getData() ?: new ArrayCollection();

        $is_agent_group  = $form->getConfig()->getOption('is_agent_group');
        $filtered_groups = $all_groups->filter(function (Usergroup $user_group) use ($is_agent_group) {
            return $user_group->is_agent_group === $is_agent_group;
        });

        $event->setData($filtered_groups);
    }

    /**
     * @param FormEvent $event
     */
    public function onMergeValues(FormEvent $event)
    {
        $form   = $event->getForm();
        $config = $form->getConfig();

        $property_path  = $config->getOption('property_path') ?: $form->getName();
        $is_agent_group = $config->getOption('is_agent_group');

        /** @var ArrayCollection $all_groups */
        $all_groups  = $config->getOption('owner')->$property_path;
        $type_groups = $event->getData();

        /** @var Usergroup $user_group */
        foreach ($type_groups as $user_group) {
            if (!$all_groups->contains($user_group)) {
                $all_groups->add($user_group);
            }
        }
        foreach ($all_groups as $user_group) {
            if ($user_group->is_agent_group === $is_agent_group && !$type_groups->contains($user_group)) {
                $all_groups->removeElement($user_group);
            }
        }

        $event->setData($all_groups);
    }
}
