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

use Orb\Util\Arrays;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

/**
 */
class TextSnippet extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 */
	protected $id = null;

	/**
	 * Who created the snippet
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person = null;

	/**
	 * @var \Application\DeskPRO\Entity\TextSnippetCategory
	 */
	protected $category;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $snippet;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	protected function process(array $options)
	{
		$options = new \Orb\Util\OptionsArray($options);
		$options->setDefault('wrap_left', '');
		$options->setDefault('wrap_right', '');
		$options->setDefault('is_html', false);

		$person_context = $options->get('person_context');
		if (!$person_context) {
			$person_context = App::getCurrentPerson();
		}

		$d = $person_context->getDateTime();

		$repl = array_merge(array(
			'var.time'      => date('h:ia', $d->getTimestamp()),
			'var.time24'    => date('H:i', $d->getTimestamp()),
			'var.date'      => date('F d, Y', $d->getTimestamp()),
			'me.name'       => $person_context->getDisplayName(),
			'me.email'      => $person_context->getPrimaryEmailAddress(),
		), $options->get('replacements', array()));

		$wrap_l = $options->get('wrap_left');
		$wrap_r = $options->get('wrap_right');
		$is_html = $options->get('is_html');

		$snippet = $this->snippet;
		if ($is_html) {
			$snippet = nl2br(htmlspecialchars($this->snippet));
		}

		foreach ($repl as $k => $v) {
			if ($is_html) {
				$v = nl2br(htmlspecialchars($v));
			}
			$snippet = str_replace("{{ $k }}", $wrap_l . $v . $wrap_r, $snippet);
			$snippet = str_replace("{{{$k}}}", $wrap_l . $v . $wrap_r, $snippet);
		}

		return $snippet;
	}

	/**
	 * @param array $options
	 * @return void
	 */
	public function format(array $options = array())
	{
		return $this->process($options);
	}

	public function formatHtml(array $options = array())
	{
		$options = array_merge($options, array('is_html' => true));
		return $this->format($options);
	}

	/**
	 * Format a snippet for displaying as a preview. This is where the terms are highlighed.
	 *
	 * @param array $options
	 * @return void
	 */
	public function formatPreviewHtml(array $options = array())
	{
		$options = array_merge($options, array('wrap_left' => '<span class="replacement">', 'wrap_right' => '</span>', 'is_html' => true));
		return $this->format($options);
	}



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TextSnippet';
		$metadata->setPrimaryTable(array( 'name' => 'text_snippets', ));
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true, ));
		$metadata->mapField(array( 'fieldName' => 'title', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'title', ));
		$metadata->mapField(array( 'fieldName' => 'snippet', 'type' => 'text', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'snippet', ));
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
		$metadata->mapManyToOne(array( 'fieldName' => 'person', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'person_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ),  ));
		$metadata->mapManyToOne(array( 'fieldName' => 'category', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TextSnippetCategory', 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'category_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => NULL, ), ),  ));
	}
}
