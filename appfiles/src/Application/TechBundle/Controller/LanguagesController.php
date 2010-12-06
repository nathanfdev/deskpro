<?php

namespace Application\TechBundle\Controller;

use \Orb\Util\Arrays;
use \Symfony\Component\Form;

class LanguagesController extends AbstractController
{
	/**
	 * @var array
	 */
	protected $lang_hierarchy = array();

	protected function init()
	{
		parent::init();

		$this->lang_hierarchy = $this->db->fetchAllKeyed("SELECT id, parent_id, locale, title FROM languages ORDER BY title ASC");
		$this->lang_hierarchy = Arrays::intoHierarchy($this->lang_hierarchy);
		$this->lang_hierarchy = Arrays::flattenHierarchy($this->lang_hierarchy);

		$this->tplvars['all_langs'] = $this->lang_hierarchy;
	}


	
	############################################################################
	# index
	############################################################################

	/**
	 * Shows a list of current langs
	 */
	public function indexAction()
    {
		if (!$this->lang_hierarchy) {
			return $this->redirect($this->generateUrl('tech_admin_langs_intro', array()));
		}

        return $this->render('TechBundle:Languages:index.twig');
    }



	############################################################################
	# intro
	############################################################################

	/**
	 * Shows an introduction to what langs are etc. A user is automatically redirected
	 * here when no langs exist yet.
	 */
	public function introAction()
	{
		$this->tplvars['has_no_langs'] = !((bool)$this->lang_hierarchy);

		return $this->render('TechBundle:Languages:intro.twig');
	}



	############################################################################
	# id/edit | new-language
	############################################################################

	/**
	 * Form
	 */
	public function editLangAction($lang_id)
	{
		#-------------------------
		# Get the lang we're working on
		#-------------------------

		if ($lang_id) {
			$lang = $this->getLangOr404($lang_id);
		} else {
			$lang = new \Application\CoreBundle\Entity\Language();
		}

		$this->tplvars['lang'] = $lang;


		#-------------------------
		# Set up the form and validator
		#-------------------------

		$form = new Form\Form('lang', $lang, $this->get('validator'));
		$form->add(new Form\TextField('title'));
		$form->add(new Form\TextField('locale'));

		if (!$lang['id'] AND $this->lang_hierarchy) {
			foreach ($this->lang_hierarchy as $s) {
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

		if ($this->get('request')->getMethod() == 'POST') {
			$form->bind($this->get('request')->request->get('lang'));
			if ($form->isValid()) {
				$this->em->persist($lang);
				$this->em->flush();

				return $this->redirect($this->generateUrl('tech_admin_langs_phrases', array('lang_id' => $lang['id'])));
			}
		}

		return $this->render('TechBundle:Languages:edit.twig');
	}


	############################################################################
	# id/phrases
	############################################################################

	/**
	 * List phrases
	 */
	public function listPhrasesAction($lang_id)
	{
		$lang = $this->getLangOr404($lang_id);
		$this->tplvars['lang'] = $lang;

		$lang_finder = new \Application\DeskPRO\ResourceScanner\LanguageFiles($this->container);
		$bundled_groups = $lang_finder->getGroups();
		
		$phrases = array();

		foreach ($bundled_groups as $bundle => $groups) {
			foreach ($groups as $group => $groupfile) {
				$phrases[$group] = $lang_finder->getPhrasesInFile($groupfile);
			}
		}

		$this->tplvars['all_phrases'] = $phrases;

		return $this->render('TechBundle:Languages:language-phrase-list.twig');
	}


	############################################################################
	# id/phrases/somephrasename
	############################################################################

	/**
	 * Edit a phrase
	 */
	public function editPhraseAction($lang_id, $phrase_name)
	{
		$lang = $this->getLangOr404($lang_id);
		$this->tplvars['lang'] = $lang;

		$lang_finder = new \Application\DeskPRO\ResourceScanner\LanguageFiles($this->container);
		$bundled_groups = $lang_finder->getGroups();

		$bundled_groups = Arrays::flatten($bundled_groups);
		if (!isset($bundled_groups[$phrase_name])) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no phrase called `$phrase_name`");
		}

		$this->tplvars['phrase_name'] = $phrase_name;

		$group_phrases = $lang_finder->getPhrasesInFile($bundled_groups[$phrase_name]);

		// TODO fetch current contents
		$this->tplvars['phrase_content'] = $group_phrases[$phrase_name];

		return $this->render('TechBundle:Styles:edit-phrase.twig');
	}

	

	############################################################################

	/**
	 * @return Application\CoreBundle\Entity\Language
	 */
	protected function getLangOr404($lang_id)
	{
		try {
			$lang = $this->em->createQuery('
				SELECT l
				FROM DeskPRO:Language l
				WHERE l.id = ?1'
			)->setParameter(1, $lang_id)->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no language with ID $lang_id");
		}

		return $lang;
	}
}