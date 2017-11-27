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

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
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

/**
 * Class UsergroupsType.
 */
class UsergroupsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onFilterValues']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onMergeValues'], 100);
    }

    /**
     * {@inheritdoc}
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['owner', 'is_agent_group'])
            ->setAllowedTypes('is_agent_group', 'bool')
            ->setAllowedTypes('owner', [Person::class, Organization::class])
            ->setDefaults([
                'class'         => Usergroup::class,
                'multiple'      => true,
                'mapped'        => false,
                'query_builder' => function (EntityRepository $repository, $options) {
                    return $repository
                        ->createQueryBuilder('u')
                        ->where('u.is_agent_group = :is_agent_group')
                        ->andWhere('u.is_enabled = 1')
                        ->andWhere("u.sys_name NOT IN ('everyone', 'registered') OR u.sys_name IS NULL")
                        ->setParameter('is_agent_group', $options['is_agent_group'])
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
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * @param FormEvent $event
     */
    public function onFilterValues(FormEvent $event)
    {
        $owner = $this->getOwner($event);

        if ($this->isAgent($event)) {
            $event->setData($owner->getPublicAgentgroups());
        } else {
            $event->setData($owner->getPublicUsergroups());
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onMergeValues(FormEvent $event)
    {
        /** @var Person|Organization $owner */
        $owner   = $this->getOwner($event);
        $oldData = $this->isAgent($event) ? $owner->getPublicAgentgroups() : $owner->getPublicUsergroups();
        $newData = $event->getData();

        /** @var ArrayCollection $usergroups */
        $usergroups = $owner->getUsergroups();

        foreach ($oldData as $group) {
            if (!$newData->contains($group)) {
                $usergroups->removeElement($group);
            }
        }
        foreach ($newData as $group) {
            if (!$oldData->contains($group)) {
                $usergroups->add($group);
            }
        }
    }

    /**
     * @param FormEvent $event
     *
     * @return bool
     */
    private function isAgent(FormEvent $event)
    {
        return $event->getForm()->getConfig()->getOption('is_agent_group');
    }

    /**
     * @param FormEvent $event
     *
     * @return mixed
     */
    private function getOwner(FormEvent $event)
    {
        return $event->getForm()->getConfig()->getOption('owner');
    }
}
