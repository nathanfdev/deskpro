<?php

namespace Application\TechBundle\Form\Style;

use Symfony\Components\Form\TextField;
use Symfony\Components\Form\PasswordField;
use Symfony\Components\Form\ChoiceField;

class Style extends \Symfony\Components\Form\Form
{
	public function configure()
	{
		$this->add(new TextField('title', array('required' => true)));
		$this->add(new ChoiceField('parent_id', array('choices' => array())));
		$this->add(new TextField('note'));
	}

	public function setParentIdOptions(array $style_hierarchy)
	{
		if (!$style_hierarchy) return;

		$f = $this->get('parent_id');

		foreach ($style_hierarchy as $id => $styleinfo) {
			if ($styleinfo['depth']) {
				$label = \str_repeat('--', $style_hierarchy['depth']) . ' ' . $styleinfo['title'];
			} else {
				$label = $styleinfo['title'];
			}

			$f->newChoiceField($id, $label);
		}
	}
}