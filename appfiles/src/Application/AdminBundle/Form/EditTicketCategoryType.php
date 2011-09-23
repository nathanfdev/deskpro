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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class EditTicketCategoryType extends AbstractType
{
	protected $category;

	public function __construct($category)
	{
		$this->category = $category;
	}

	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add('title', 'text');

		$category = $this->category;
		if (!$category['id']) {
			$parent_options = App::getDb()->fetchAllKeyValue("
				SELECT id, title
				FROM ticket_categories
				WHERE parent_id IS NULL
				ORDER BY title ASC
			");

			if ($parent_options) {
				Arrays::unshiftAssoc($parent_options, 0, '(none)');
				$builder->add('parent_id', 'choice', array('choices' => $parent_options));
			}
		}
	}
}
