<?php

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
