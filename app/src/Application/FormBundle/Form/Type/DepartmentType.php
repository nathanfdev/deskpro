<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\FormBundle\Form\Type;

use Application\FormBundle\Form\DataTransformer\EntityToIdTransformer;
use Application\FormBundle\Hierarchy\HierarchyGenerator;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotNull;

class DepartmentType extends AbstractType
{
    /**
     * @var \Application\FormBundle\Hierarchy\HierarchyGenerator
     */
    private $hierarchy_generator;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(HierarchyGenerator $hierarchy, EntityManager $em)
    {
        $this->hierarchy_generator = $hierarchy;
        $this->em = $em;
    }

    public function getName()
    {
        return 'deskpro_department';
    }

    public function getParent()
    {
        return 'choice';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new EntityToIdTransformer($this->em->getRepository('DeskPRO:Department')));;
    }


    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $hierarchy_generator = $this->hierarchy_generator;

        $resolver->setRequired(array('person'));

        $resolver->setDefaults(array(
            'class'         => 'Application\\DeskPRO\\Entity\\Department',
            'property'      => 'title',
            'placeholder'    => 'Select...',
            'required' => true,
            'constraints' => array(
                new NotNull()
            ),
            'hierarchy' => function (Options $options) use ($hierarchy_generator) {
                return $hierarchy_generator->generateTicketDepartmentsHierarchy($options['person']);
            },
            'choice_list'   => function(Options $options) {
                    return $options['hierarchy']->getChoiceList();
            }
        ));
    }
}
 