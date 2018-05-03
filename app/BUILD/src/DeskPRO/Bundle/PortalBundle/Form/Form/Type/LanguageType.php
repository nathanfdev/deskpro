<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Application\DeskPRO\Entity\Language;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class LanguageType.
 */
class LanguageType extends AbstractType
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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('view_context')
            ->setAllowedValues('view_context', ['user', 'agent', 'admin'])
            ->setDefaults([
                'property_path' => 'language',
                'class'         => Language::class,
                'choice_label'  => 'title',
                'empty_data'    => null,
                'query_builder' => function (Options $options) {
                    return function (EntityRepository $repo) use ($options) {
                        $query = $repo->createQueryBuilder('l')->select('l');

                        switch ($options['view_context']) {
                            case 'user':
                                $query->andWhere('l.has_user = true');
                                break;
                            case 'agent':
                                $query->andWhere('l.has_agent = true');
                                break;
                            case 'admin':
                                $query->andWhere('l.has_admin = true');
                                break;
                        }

                        return $query;
                    };
                },
            ])
        ;
    }
}
