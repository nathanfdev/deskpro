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

use Doctrine\ORM\Mapping as ORM_Mapping;

use Application\DeskPRO\App;

/**
 * A widget is a Javascript widget added to various pages.
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\Widget")
 * @ORM_Mapping\Table(name="widgets")
 */
class Widget extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 *
	 */
	protected $id = null;

	/**
	 * The widgets name id. This is a name that identifies the specific
	 * type of widget. The author of the widget should name it. For example,
	 * 'com_example_getuser'
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="name_id", type="string", length=200)
	 */
	protected $name_id = '';

	/**
	 * Note/title for this widget, reminder for admin
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="note", type="string", length=255)
	 */
	protected $note = '';

	/**
	 * An array of CSS files this widget loads.
	 *
	 * @var array
	 * @ORM_Mapping\Column(name="assets_css", type="array")
	 */
	protected $assets_css = array();

	/**
	 * An array of JS files this widget loads.
	 *
	 * @var array
	 * @ORM_Mapping\Column(name="assets_js", type="array")
	 */
	protected $assets_js = array();

	/**
	 * An array of other data that might be used in templates
	 *
	 * @var array
	 * @ORM_Mapping\Column(name="data", type="array")
	 */
	protected $data = array();

	/**
	 * The page/section this widget should be displayed on.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="section", type="string", length=200)
	 */
	protected $section;

	/**
	 * The JS classname that contains the widget handler.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="js_widget_class", type="string", length=200, nullable=true)
	 */
	protected $js_widget_class;

	/**
	 * The PHP class that handles fetch data/processing
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="php_widget_class", type="string", length=200, nullable=true)
	 */
	protected $php_widget_class;

	/**
	 * The template used to render the widget HTML.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="template_name", type="string", length=200, nullable=true)
	 */
	protected $template_name;


	/**
	 * Get the handler for this widget class.
	 *
	 * @return Application\DeskPRO\Widgets\HandlerInterface
	 */
	public function getHandler(array $options)
	{
		if ($this->php_widget_class) {
			$classname = $this->php_widget_class;
		} else {
			$classname = 'Application\\DeskPRO\\Widgets\\WidgetHandler';
		}

		$handler = new $classname($this, $options);

		return $handler;
	}
}
