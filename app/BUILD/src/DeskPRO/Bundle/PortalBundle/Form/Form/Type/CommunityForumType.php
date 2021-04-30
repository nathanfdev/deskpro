<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use DeskPRO\Bundle\AppBundle\Form\Type\EntityHierarchyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommunityForumType.
 */
class CommunityForumType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('person')
            ->setDefaults([
                'person'        => null,
                'choice_loader' => function (Options $options) {
                    /** @var \DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator $hierarchyGenerator */
                    $hierarchyGenerator = $options['hierarchy_generator'];

                    return $hierarchyGenerator->generateForCommunityForums($options['person'])->getChoiceLoader();
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
