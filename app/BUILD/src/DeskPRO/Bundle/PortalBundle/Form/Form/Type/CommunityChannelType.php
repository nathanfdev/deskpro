<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\Type\EntityHierarchyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommunityChannelType.
 */
class CommunityChannelType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('person')
            ->setAllowedTypes('person', Person::class)
            ->setDefaults([
                'choice_loader' => function (Options $options) {
                    /** @var \DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator $hierarchyGenerator */
                    $hierarchyGenerator = $options['hierarchy_generator'];

                    return $hierarchyGenerator->generateForCommunityChannels($options['person'])->getChoiceLoader();
                },
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntityHierarchyType::class;
    }
}
