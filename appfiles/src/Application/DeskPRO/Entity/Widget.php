<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use \Application\DeskPRO\App;

/**
 * A widget is a Javascript widget added to various pages.
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\Widget")
 * @orm:Table(name="widgets")
 */
class Widget extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * The widgets name id. This is a name that identifies the specific
	 * type of widget. The author of the widget should name it. For example,
	 * 'com_example_getuser'
	 *
	 * @var string
	 * @orm:Column(name="name_id", type="string", length=200)
	 */
	protected $name_id = '';

	/**
	 * An array of CSS files this widget loads.
	 *
	 * @var array
	 * @orm:Column(name="assets_css", type="array")
	 */
	protected $assets_css = array();

	/**
	 * An array of JS files this widget loads.
	 *
	 * @var array
	 * @orm:Column(name="assets_js", type="array")
	 */
	protected $assets_js = array();

	/**
	 * An array of other data that might be used in templates
	 *
	 * @var array
	 * @orm:Column(name="data", type="array")
	 */
	protected $data = array();

	/**
	 * The page/section this widget should be displayed on.
	 *
	 * @var string
	 * @orm:Column(name="section", type="string", length=200)
	 */
	protected $section;

	/**
	 * The JS classname that contains the widget handler.
	 *
	 * @var string
	 * @orm:Column(name="js_widget_class", type="string", length=200)
	 */
	protected $js_widget_class;

	/**
	 * The template used to render the widget HTML
	 *
	 * @var string
	 * @orm:Column(name="template_name", type="string", length=200)
	 */
	protected $template_name;



	/**
	 * Get preferences for this widget based on the current person.
	 * This will fetch from the person prefs, and then from session.
	 *
	 * @return array
	 */
	public function getPrefsForCurrentPerson()
	{
		$prefs = array();

		$pref_prefix = 'widget.' . $this->name_id . '.';

		// From person
		$person = App::getCurrentPerson();
		if ($person AND $person['id']) {
			$prefs = $person->loadPrefGroup($pref_prefix);
		}

		// From session
		$session = App::getSession();
		if ($session) {
			foreach ($session->getAttributes() as $k => $v) {
				if (strpos($k, $pref_prefix) === 0) {
					$k = str_replace($k, '', $k);
					$prefs[$k] = $v;
				}
			}
		}

		return $prefs;
	}
}