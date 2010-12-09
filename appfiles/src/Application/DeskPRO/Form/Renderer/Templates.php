<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Form
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Form\Renderer;

class Templates implements RendererInterface
{
	/**
	 * Template namespace (aka prefix)
	 * @var string
	 */
	protected $template_ns;

	/**
	 * @var Application\DeskPRO\Templating\Engine
	 */
	protected $template_engine;



	/**
	 *
	 * @param \Application\DeskPRO\Templating\Engine $temmplate_engine
	 * @param string $template_ns
	 */
	public function __construct(\DeskPRO\Templating\Engine $temmplate_engine, $template_ns)
	{
		$this->template_ns = $template_ns;
		$this->template_engine = $temmplate_engine;
	}


	
	/**
	 * Render the field
	 *
	 * @param  Orb\Form\Field\Field $field       The field that needs rendering
	 * @param  array                $attributes  Attributes of the field
	 * @return string HTML
	 */
	public function renderField(\Orb\Form\Field\Field $field, array $attributes = array())
	{
		// The short classname is just the classname without the namespace,
		// UNLESS the namespace isn't DeskPRO or Orb (meaning its custom)
		// then its the full name
		$short_classname = get_class($field);

		if (strpos($short_classname, 'Orb\\') === 0 OR strpos($short_classname, 'Application\\DeskPRO\\') === 0) {
			$parts = explode('\\', $short_classname);
			$short_classname = array_pop($parts);
		}

		// So we end up with templates called...
		// AgentBundle:Whatever:Text, AgentBundle:Whatever:Date

		return $this->template_engine->render(
			$this->template_ns . ':' . $short_classname,
			array('field' => $field, 'attributes' => $attributes)
		);
	}
}