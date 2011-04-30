<?php

namespace Application\AdminBundle\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Application\AdminBundle\Form\EditLanguageType;
use \Orb\Util\Arrays;
use \Symfony\Component\Form;

class LanguagesController extends AbstractController
{
	/**
	 * @var array
	 */
	protected $language_hierarchy = array();

	protected function init()
	{
		parent::init();

		$this->_setHierarchyVar();
	}

	protected function _setHierarchyVar()
	{
		$this->language_hierarchy = $this->db->fetchAllKeyed("SELECT id, parent_id, title FROM languages ORDER BY title ASC");
		$this->language_hierarchy = Arrays::intoHierarchy($this->language_hierarchy);
		$this->language_hierarchy = Arrays::flattenHierarchy($this->language_hierarchy);
	}

	############################################################################
	# list languages
	############################################################################

	/**
	 * Shows a list of current langs
	 */
	public function listLanguagesAction()
    {
		$this->rememberLastPage();

        return $this->render('AdminBundle:Languages:list-languages.html.twig', array(
			'language_hierarchy' => $this->language_hierarchy
		));
    }


	############################################################################
	# edit language
	############################################################################

	/**
	 * Edit a language
	 */
	public function editLanguageAction($language_id)
	{
		#-------------------------
		# Get the style we're working on
		#-------------------------

		if ($language_id) {
			$language = $this->getLanguageOr404($language_id);
		} else {
			$language = new \Application\DeskPRO\Entity\Language();
		}

		$form = $this->get('form.factory')->create(new EditLanguageType($language), $language);

		$is_edited = false;
		$row_html = false;
		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$is_edited = true;
				App::getOrm()->persist($language);
				App::getOrm()->flush();

				$this->_setHierarchyVar();// reset data in hierarchy
				$row_html = $this->renderView('AdminBundle:Languages:list-languages-row.html.twig', array('language' => $this->language_hierarchy[$language['id']]));

				// Recreate form because parent_id field cant be changed, so we need to get rid of it
				$form = $this->get('form.factory')->create(new EditLanguageType($language), $language);
			}
		}

		return $this->render('AdminBundle:Languages:edit-language.html.twig', array(
			'language' => $language,
			'form'      => $form->createView(),
			'is_edited' => $is_edited,
			'row_html'  => $row_html
		));
	}


	############################################################################
	# id/phrases
	############################################################################

	/**
	 * List phrases
	 */
	public function listPhrasesAction($language_id)
	{
		$language = $this->getLanguageOr404($language_id);

		$lang_finder = new \Application\DeskPRO\ResourceScanner\LanguageFiles();
		//$changed_templates = $style->getCustomTemplateNames();

		return $this->render('AdminBundle:Languages:list-phrasegroups.html.twig', array(
			'language' => $language,
			'group_files' => $lang_finder->getGroupsInAllBundles(),
			//'changed_templates' => $changed_templates
		));
	}


	############################################################################
	# edit phrase group
	############################################################################

	/**
	 * Edit a phrase
	 */
	public function editPhraseGroupAction($language_id)
	{
		$language = $this->getLanguageOr404($language_id);

		$phrasegroup = $this->in->getString('phrasegroup');
		$lang_finder = new \Application\DeskPRO\ResourceScanner\LanguageFiles();

		$phrase_file = $lang_finder->getPathForGroup($phrasegroup);
		if (!is_file($phrase_file)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no phrase group with that name");
		}

		$orig_phrases = require($phrase_file);
		$lang_phrases = App::getOrm()->createQuery("
			SELECT p
			FROM DeskPRO:Phrase p INDEX BY p.name
			WHERE p.language = ?1 AND p.groupname = ?2
		")->execute(array(1=>$language, 2=>$phrasegroup));

		if ($this->in->getBool('process')) {

			App::getOrm()->beginTransaction();

			foreach ($orig_phrases as $phrase_name => $orig_phrase) {
				$editted = $this->in->getString(array('trans', $phrase_name));
				$phrase = null;
				if (isset($lang_phrases[$phrase_name])) {
					$phrase = $lang_phrases[$phrase_name];
				}

				// not edited, remove
				if (!$editted OR $editted == $orig_phrase) {
					if ($phrase) {
						App::getOrm()->remove($phrase);
						unset($lang_phrases[$phrase_name]);
					}

				// edited, create or update
				} else {
					if (!$phrase) {
						$phrase = new Entity\Phrase();
						$phrase['language'] = $language;
						$phrase['name'] = $phrase_name;
					}

					$phrase['phrase'] = $editted;
					App::getOrm()->persist($phrase);
				}
			}

			App::getOrm()->flush();
			App::getOrm()->commit();
		}

		return $this->render('AdminBundle:Languages:edit-phrasegroup.html.twig', array(
			'language' => $language,
			'phrasegroup' => $phrasegroup,
			'orig_phrases' => $orig_phrases,
			'lang_phrases' => $lang_phrases
		));
	}



	############################################################################

	/**
	 * @return Application\DeskPRO\Entity\Language
	 */
	protected function getLanguageOr404($language_id)
	{
		try {
			$language = $this->em->createQuery('
				SELECT l
				FROM DeskPRO:Language l
				WHERE l.id = ?1'
			)->setParameter(1, $language_id)->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no language with ID $language_id");
		}

		return $language;
	}
}