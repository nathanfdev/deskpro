<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\FormBundle\Form\Type;


use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LanguageType extends AbstractType
{
    public function getName()
    {
        return 'deskpro_language';
    }

    public function getParent()
    {
        return 'entity';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('view_context'));
        $resolver->setAllowedValues(array('view_context' => array('user', 'agent', 'admin')));
        $resolver->setDefaults(array(
            'property_path' => 'language',
            'class'         => 'Application\\DeskPRO\\Entity\\Language',
            'property'      => 'title',
            'empty_data'    => null,
            'query_builder' => function (Options $options) {
                    return function (EntityRepository $repo) use ($options) {
                        $query = $repo
                            ->createQueryBuilder('l')
                            ->select('l');
                        if ('user' === $options->get('view_context')) {
                            $query->andWhere('l.has_user = true');
                        }
                        if ('agent' === $options->get('view_context')) {
                            $query->andWhere('l.has_agent = true');
                        }
                        if ('admin' === $options->get('view_context')) {
                            $query->andWhere('l.has_admin = true');
                        }

                        return $query;
                    };
                }
        ));
    }
}
