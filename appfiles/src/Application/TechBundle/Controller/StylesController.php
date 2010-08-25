<?php

namespace Application\TechBundle\Controller;

use \Orb\Util\Arrays;

class StylesController extends AbstractController
{
	/**
	 * @var array
	 */
	protected $style_hierarchy = array();

	protected function init()
	{
		parent::init();
		
		$this->style_hierarchy = $this->db->fetchAllKeyed("SELECT id, parent_id, title FROM styles ORDER BY title ASC");
		$this->style_hierarchy = Arrays::intoHierarchy($this->style_hierarchy);
		$this->style_hierarchy = Arrays::flattenHierarchy($this->style_hierarchy);

		$this->tpl['all_styles'] = $this->style_hierarchy;
	}

	public function indexAction()
    {
		if (!$this->style_hierarchy) {
			return $this->redirect($this->generateUrl('tech_admin_styles_intro', array()));
		}

        return $this->render('TechBundle:Styles:index');
    }

	public function introAction()
	{
		$this->tpl['has_no_styles'] = !((bool)$this->style_hierarchy);

		return $this->render('TechBundle:Styles:intro');
	}
}
