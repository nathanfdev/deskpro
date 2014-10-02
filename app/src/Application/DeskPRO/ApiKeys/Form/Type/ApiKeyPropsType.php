<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 */

namespace Application\DeskPRO\ApiKeys\Form\Type;

use Application\DeskPRO\Entity\ApiKey;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ApiKeyPropsType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('note', 'text', array('required' => true));
		$builder->add(
			'person',
			'entity',
			array(
				 'class'         => 'DeskPRO:Person',
				 'required'      => false,
				 'multiple'      => false,
				 'property'      => 'display_name',
				 'query_builder' => function (EntityRepository $er) {
					 return $er->createQueryBuilder('p')->where(
						 'p.is_agent = true AND p.is_deleted = false'
					 );
				 }
			)
		);
		$builder->add('flags', 'choice', array(
			'choices' => array(ApiKey::FLAG_SUPER_KEY => ApiKey::FLAG_SUPER_KEY),
			'multiple' => true, // an array
			'required' => false,
		));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(
			array(
				 'data_class' => 'Application\\DeskPRO\\Entity\\ApiKey',
			)
		);
	}

	public function getName()
	{
		return 'twitter_account';
	}
}