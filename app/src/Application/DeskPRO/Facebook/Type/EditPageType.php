<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
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
 */

namespace Application\DeskPRO\Facebook\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditPageType extends AbstractType
{
	/**
	 * @param FormBuilderInterface $builder
	 * @param array                $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('app', new EditAppType());
		$builder->add('graph_id', 'text', array('required' => true));
		$builder->add('user_graph_id', 'text', array('required' => true));
		$builder->add('page_token', 'text', array('required' => true));
		$builder->add('user_token', 'text', array('required' => true));
		$builder->add('name', 'text', array('required' => true));
		$builder->add('picture_url', 'text', array('required' => true));
		$builder->add('import_wall_posts', 'checkbox', array('required' => false));
		$builder->add('disable_own_wall_posts', 'checkbox', array('required' => false));
		$builder->add('import_direct_messages', 'checkbox', array('required' => false));
		$builder->add('is_enabled', 'hidden', array('required' => false));
		$builder->add('is_connected', 'hidden', array('required' => false));
		$builder->add('is_tested', 'hidden', array('required' => false));
	}


	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(
			array(
				'data_class'         => 'Application\\DeskPRO\\Facebook\\EditPage',
				'cascade_validation' => true,
			)
		);
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return 'page';
	}
}
