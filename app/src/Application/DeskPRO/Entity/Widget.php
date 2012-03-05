<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

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



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE); 
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Widget'; 
		$metadata->setPrimaryTable(array( 'name' => 'widgets', )); 
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT); 
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'id', 'id' => true, )); 
		$metadata->mapField(array( 'fieldName' => 'name_id', 'type' => 'string', 'length' => 200, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'name_id', )); 
		$metadata->mapField(array( 'fieldName' => 'note', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'note', )); 
		$metadata->mapField(array( 'fieldName' => 'assets_css', 'type' => 'array', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'assets_css', )); 
		$metadata->mapField(array( 'fieldName' => 'assets_js', 'type' => 'array', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'assets_js', )); 
		$metadata->mapField(array( 'fieldName' => 'data', 'type' => 'array', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'data', )); 
		$metadata->mapField(array( 'fieldName' => 'section', 'type' => 'string', 'length' => 200, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'section', )); 
		$metadata->mapField(array( 'fieldName' => 'js_widget_class', 'type' => 'string', 'length' => 200, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'js_widget_class', )); 
		$metadata->mapField(array( 'fieldName' => 'php_widget_class', 'type' => 'string', 'length' => 200, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'php_widget_class', )); 
		$metadata->mapField(array( 'fieldName' => 'template_name', 'type' => 'string', 'length' => 200, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'template_name', )); 
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
	}
}

