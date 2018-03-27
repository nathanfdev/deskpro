<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Organizations;

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\OrganizationNote;
use Application\DeskPRO\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrganizationNoteType.
 */
class OrganizationNoteType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('note', TextType::class, [
            'required' => true,
        ]);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit'], 100);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => OrganizationNote::class,
            ])
            ->setRequired(['agent', 'organization'])
            ->setAllowedTypes('agent', Person::class)
            ->setAllowedTypes('organization', Organization::class)
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $data   = $event->getData();
        $config = $event->getForm()->getConfig();

        if ($data instanceof OrganizationNote && !$data->getId()) {
            $data->setOrganization($config->getOption('organization'));
            $data->setAgent($config->getOption('agent'));
        }
    }
}
