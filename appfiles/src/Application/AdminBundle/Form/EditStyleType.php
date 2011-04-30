<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\Form;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class EditStyleType extends AbstractType
{
	protected $style;

	public function __construct($style)
	{
		$this->style = $style;
	}

	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add('title', 'text');
		$builder->add('note', 'text');

		$style = $this->style;
		if (!$style['id']) {
			$parent_options = App::getDb()->fetchAllKeyValue("
				SELECT id, title
				FROM styles
				ORDER BY title ASC
			");

			if ($parent_options) {
				Arrays::unshiftAssoc($parent_options, 0, '(none)');
				$builder->add('parent_id', 'choice', array('choices' => $parent_options));
			}
		}
	}
}