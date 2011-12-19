<?php

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;
use Application\AdminBundle\Form\EditStyleType;
use Orb\Util\Arrays;
use Symfony\Component\Form;

class StylesController extends AbstractController
{
	/**
	 * @var array
	 */
	protected $style_hierarchy = array();

	protected function init()
	{
		parent::init();

		$this->_setHierarchyVar();
	}

	protected function _setHierarchyVar()
	{
		$this->style_hierarchy = $this->db->fetchAllKeyed("SELECT id, parent_id, title, note FROM styles ORDER BY title ASC");
		$this->style_hierarchy = Arrays::intoHierarchy($this->style_hierarchy);
		$this->style_hierarchy = Arrays::flattenHierarchy($this->style_hierarchy);
	}

	############################################################################
	# list styles
	############################################################################

	/**
	 * Shows a list of currents styles
	 */
	public function listStylesAction()
    {
        return $this->render('AdminBundle:Styles:list-styles.html.twig', array(
			'style_hierarchy' => $this->style_hierarchy
		));
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
			$style = new \Application\DeskPRO\Entity\Style();
		}

		$form = $this->get('form.factory')->create(new EditStyleType($style), $style);

		$is_edited = false;
		$row_html = false;
		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$is_edited = true;
				App::getOrm()->persist($style);
				App::getOrm()->flush();

				$this->_setHierarchyVar();// reset data in hierarchy
				$row_html = $this->renderView('AdminBundle:Styles:list-styles-row.html.twig', array('style' => $this->style_hierarchy[$style['id']]));

				// Recreate form because parent_id field cant be changed, so we need to get rid of it
				$form = $this->get('form.factory')->create(new EditStyleType($style), $style);
			}
		}

		return $this->render('AdminBundle:Styles:edit-style.html.twig', array(
			'style' => $style,
			'form'      => $form->createView(),
			'is_edited' => $is_edited,
			'row_html'  => $row_html
		));
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

		$template_finder = new \Application\DeskPRO\ResourceScanner\TemplateFiles();
		$changed_templates = $style->getCustomTemplateNames();

		return $this->render('AdminBundle:Styles:list-templates.html.twig', array(
			'style' => $style,
			'template_files' => $template_finder->getTempaltesInAllBundles(),
			'changed_templates' => $changed_templates
		));
	}


	############################################################################
	# id/templates/some:template:name
	############################################################################

	/**
	 * Edit a template
	 */
	public function editTemplateAction($style_id)
	{
		$style = $this->getStyleOr404($style_id);

		$template_name = $this->in->getString('template_name');

		$template_finder = new \Application\DeskPRO\ResourceScanner\TemplateFiles();
		$template_file = $template_finder->getPathForTemplate($template_name);

		if (!is_file($template_file)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no template with that name");
		}

		$default_template_contents = file_get_contents($template_file);
		$template = $style->getTemplate($template_name);
		if ($template) {
			$template_contents = $template['template'];
		} else {
			$template_contents = $default_template_contents;
		}

		if ($this->in->getBool('process')) {
			if (!$template) {
				$template = $style->getTemplateObject($template_name);
			}

			$template['template'] = $this->in->getString('template_contents');

			App::getOrm()->persist($template);
			App::getOrm()->flush();

			$template_contents = $template['template'];
		}

		return $this->render('AdminBundle:Styles:edit-template.html.twig', array(
			'style' => $style,
			'template_name' => $template_name,
			'template_contents' => $template_contents,
			'default_template_contents' => $default_template_contents
		));
	}

	public function revertTemplateAction($style_id)
	{
		$style = $this->getStyleOr404($style_id);
		$template_name = $this->in->getString('template_name');

		$template = $style->getTemplate($template_name);
		if ($template) {
			App::getOrm()->remove($template);
			App::getOrm()->flush();
		}

		return $this->redirectRoute('admin_styles_templates', array('style_id' => $style['id']));
	}


	############################################################################
	# editor-popup
	############################################################################

	public function editorPopupAction()
	{
		$messenger_id = $this->in->getString('opener_id');

		$template_code = '';
		$template_orig_code = '';

		if ($this->in->getString('template')) {
			$template = App::getEntityRepository('DeskPRO:Template')->getTemplateForStyle($this->in->getString('template'));
			if ($template) {
				$template_code = $template['template'];
			}
		}
		if ($this->in->getString('template_orig')) {
			$template_orig = App::getEntityRepository('DeskPRO:Template')->getTemplateForStyle($this->in->getString('template_orig'));
			if ($template_orig) {
				$template_orig_code = $template_orig['template'];
			}
		}

		if (!$template_orig_code AND $this->in->getString('template_orig')) {
			$template_name_parser = $this->get('templating.name_parser');
			$template_locator = $this->get('templating.locator');
			try {
				$template_file_ref = $template_name_parser->parse($this->in->getString('template_orig'));
				$template_file = $template_locator->locate($template_file_ref);
				$template_orig_code = file_get_contents($template_file);
			} catch (\Exception $e) {}
		}

		if (App::getSetting('core.single_lang_mode')) {
			$dephrase = new \Application\DeskPRO\Translate\DephrasifyTemplate(App::getTranslator());
			$template_code = $dephrase->expand($template_code);
			$template_orig_code = $dephrase->expand($template_orig_code);
		}

		return $this->render('AdminBundle:Styles:editor-popup.html.twig', array(
			'messenger_id' => $messenger_id,
			'template_code' => $template_code,
			'template_orig_code' => $template_orig_code,
		));
	}





	############################################################################

	/**
	 * @return Application\DeskPRO\Entity\Style
	 */
	protected function getStyleOr404($style_id)
	{
		try {
			$style = $this->em->createQuery('
				SELECT s
				FROM DeskPRO:Style s
				WHERE s.id = ?1'
			)->setParameter(1, $style_id)->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no style with ID $style_id");
		}

		return $style;
	}
}
