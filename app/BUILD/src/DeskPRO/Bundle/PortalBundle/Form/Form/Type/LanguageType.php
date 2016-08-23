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
                'property'      => 'title',
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
