<?php

namespace Application\TechBundle\Controller;

use \Orb\Util\Arrays;
use \Symfony\Component\Form;

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

		$this->tplvars['all_styles'] = $this->style_hierarchy;
	}



	############################################################################
	# index
	############################################################################

	/**
	 * Shows a list of currents styles
	 */
	public function indexAction()
    {
		if (!$this->style_hierarchy) {
			return $this->redirect($this->generateUrl('tech_admin_styles_intro', array()));
		}

        return $this->render('TechBundle:Styles:index');
    }


	
	############################################################################
	# intro
	############################################################################

	/**
	 * Shows an introduction to what styles are etc. A user is automatically redirected
	 * here when no styles exist yet.
	 */
	public function introAction()
	{
		$this->tplvars['has_no_styles'] = !((bool)$this->style_hierarchy);

		return $this->render('TechBundle:Styles:intro');
	}



	############################################################################
	# id/edit-style | new-style
	############################################################################

	/**
	 * Form
	 */
	public function editStyleAction($style_id)
	{
		#-------------------------
		# Get the style we're working on
		#-------------------------

		if ($style_id) {
			$style = $this->getStyleOr404($style_id);
		} else {
			$style = new \Application\CoreBundle\Entity\Style();
		}

		$this->tplvars['style'] = $style;

		
		#-------------------------
		# Set up the form and validator
		#-------------------------

		$form = new Form\Form('style', $style, $this['validator']);
		$form->add(new Form\TextField('title'));

		if (!$style['id'] AND $this->style_hierarchy) {
			foreach ($this->style_hierarchy as $s) {
				$indent = '';
				if ($s['depth']) $indent = str_repeat('--', $s['depth']) . ' ';

				$choices[$s['id']] = $indent . $s['title'];
			}

			$f = new Form\ChoiceField('parent_id', array('choices' => $choices));
			$form->add($f);
		}
		$form->add(new Form\TextAreaField('note'));

		$this->tplvars['form'] = $form;

		
		#-------------------------
		# If the form was submitted, try and save it
		#-------------------------

		if ($this['request']->getMethod() == 'POST') {
			$form->bind($this['request']->request->get('style'));
			if ($form->isValid()) {
				$this->em->persist($style);
				$this->em->flush();

				return $this->redirect($this->generateUrl('tech_admin_styles_showstyle', array('style_id' => $style['id'])));
			}
		}

		return $this->render('TechBundle:Styles:edit');
	}



	############################################################################
	# id/templates
	############################################################################

	/**
	 * Shows template list
	 */
	public function styleTemplateListAction($style_id)
	{
		$style = $this->getStyleOr404($style_id);
		$this->tplvars['style'] = $style;

		$template_finder = new \DeskPRO\ResourceScanner\TemplateFiles($this->container);
		$this->tplvars['template_files'] = $template_finder->getTemplates(true);

		return $this->render('TechBundle:Styles:style-template-list');
	}


	############################################################################
	# id/templates/some:template:name
	############################################################################

	/**
	 * Edit a template
	 */
	public function editTemplateAction($style_id, $template_name)
	{
		$style = $this->getStyleOr404($style_id);
		$this->tplvars['style'] = $style;

		$template_finder = new \DeskPRO\Style\TemplateFileScanner($this->container);
		$template_files = $template_finder->getTemplates();

		$template_files = Arrays::flatten($template_files);
		if (!isset($template_files[$template_name])) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no template called `$template_name`");
		}

		$this->tplvars['template_name'] = $template_name;
		$this->tplvars['style'] = $style;

		// TODO fetch current styles contents
		$this->tplvars['template_content'] = file_get_contents($template_files[$template_name]);

		return $this->render('TechBundle:Styles:edit-template');
	}

	



	############################################################################

	/**
	 * @return Application\CoreBundle\Entity\Style
	 */
	protected function getStyleOr404($style_id)
	{
		try {
			$style = $this->em->createQuery('
				SELECT s
				FROM CoreBundle:Style s
				WHERE s.id = ?1'
			)->setParameter(1, $style_id)->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no style with ID $style_id");
		}

		return $style;
	}
}
