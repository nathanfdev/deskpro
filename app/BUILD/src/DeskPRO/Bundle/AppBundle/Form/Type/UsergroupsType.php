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
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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
            ->setRequired('is_agent_group')
            ->setAllowedTypes([
                'is_agent_group' => 'bool',
            ])
            ->setDefaults([
                'class'          => Usergroup::class,
                'multiple'       => true,
                'is_agent_group' => false,
                'query_builder'  => function (EntityRepository $repository, $options) {
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
}
