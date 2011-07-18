<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle\Form;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * The new ticket form
 *
 */
class NewTicketReplyType extends AbstractType
{
	/**
	 * @option array tmp_files Add new checkboxes for tmp files
	 *
	 * @param \Symfony\Component\Form\FormBuilder $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add('message', 'textarea');

		$builder->add('new_upload', 'file', array('type' => 'file', 'required' => false)); // type=file so we get UploadedFile object

		if (!empty($options['tmp_files'])) {
			$builder->add('tmp_files', 'choice', array(
				'choices' => array_combine($options['tmp_files'], $options['tmp_files']),

				// These make them checkboxes
				'multiple' => true,
				'expanded' => true,

				'required' => false,
			));
		}
	}

	public function getName()
	{
		return 'newreply';
	}
}