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
 */

namespace Application\DeskPRO\Twig\PreProcessor;

/**
 * Runs through the simplified syntax for email templates
 */
class EmailPreProcessor extends AbstractPreProcessor
{
	/**
	 * @param string $source
	 * @return string
	 */
	public function process($source)
	{
		if (strpos($source, '<dp:subject>') !== false) {
			$source = $this->getPrepend() . $source;
		}
		$source = $this->processIfblock($source);

		$source = $this->processTagAsBlock($source, 'subject', 'email_subject');
		$source = $this->processTagAsBlock($source, 'top', 'header_content');
		$source = $this->processTagAsBlock($source, 'bottom', 'footer_content');
		$source = $this->processTagAsBlock($source, 'body', 'content');

		$source = $this->processSelfTagAsMacro($source, 'spacer', 'spacer');
		$source = $this->processSelfTagAsMacro($source, 'hr', 'hr');

		$source = $this->processSetFlags($source);

		$source = $this->processTagAsMacro($source, 'section', 'section');
		$source = $this->processTagAsMacro($source, 'section-header', 'sectionHeader');

		return $source;
	}


	/**
	 * Parses tags into blocks
	 *
	 * @param string $source
	 * @param string $tagname
	 * @param string $blockname
	 * @return string
	 */
	public function processTagAsBlock($source, $tagname, $blockname)
	{
		$source = str_replace("<dp:$tagname>", "{%- block $blockname -%}", $source);
		$source = str_replace("</dp:$tagname>", "{%- endblock $blockname -%}", $source);
		return $source;
	}


	/**
	 * Parses tags content into a variable, and then includes a template with 'content' set to the set variable.
	 *
	 * @param string $source
	 * @param string $tagname
	 * @param string $tplname
	 * @return string
	 */
	public function processTagAsTpl($source, $tagname, $tplname)
	{
		$self = $this;
		$set_id_stack = array();
		$source = preg_replace_callback("#<dp:$tagname>#", function($m) use ($self, &$set_id_stack) {
			$set_id = $self->getSetId();
			$set_id_stack[] = $set_id;

			$new = "{%- set $set_id -%}";
			return $new;
		}, $source);

		$source = preg_replace_callback("#</dp:$tagname>#", function($m) use ($tplname, &$set_id_stack) {
			$set_id = array_shift($set_id_stack);
			$new = "{%- endset -%}{{ include 'DeskPRO:emails_common:$tplname.html.twig' with {content: $set_id} }}";
			return $new;
		}, $source);

		return $source;
	}


	/**
	 * Parses a self-closing tag into a macro call
	 *
	 * @param string $source
	 * @param string $tagname
	 * @param string $tplname
	 * @return string string
	 */
	public function processSelfTagAsMacro($source, $tagname, $tplname)
	{
		$source = preg_replace_callback("#<dp:$tagname\s*/>#", function($m) use ($tplname) {
			$new = "{{ layout.$tplname() }}";
			return $new;
		}, $source);

		return $source;
	}


	/**
	 * Parses tags content into a variable, and then calls a macro with first parameter the content.
	 *
	 * @param string $source
	 * @param string $tagname
	 * @param string $tplname
	 * @return string string
	 */
	public function processTagAsMacro($source, $tagname, $tplname)
	{
		$self = $this;
		$set_id_stack = array();
		$source = preg_replace_callback("#<dp:$tagname>#", function($m) use ($self, &$set_id_stack) {
			$set_id = $self->getSetId();
			$set_id_stack[] = $set_id;

			$new = "{%- set $set_id -%}";
			return $new;
		}, $source);

		$source = preg_replace_callback("#</dp:$tagname>#", function($m) use ($tplname, &$set_id_stack) {
			$set_id = array_shift($set_id_stack);
			$new = "{%- endset -%}{{ layout.$tplname($set_id) }}";
			return $new;
		}, $source);

		return $source;
	}


	/**
	 * Wraps a block of code, and only shows it if a certain block has a value (usually an inner-containing block).
	 *
	 * @param string $source
	 * @return string
	 */
	public function processIfblock($source)
	{
		$self = $this;
		$set_id_stack = array();
		$tagname = '';
		$source = preg_replace_callback("#\{%\s*ifblock\s*([a-zA-Z_]+)\s*%\}#", function($m) use (&$tagname, $self, &$set_id_stack) {
			$set_id = $self->getSetId();
			$set_id_stack[] = $set_id;
			$tagname = $m[1];

			$new = "{%- set $set_id -%}";
			return $new;
		}, $source);

		$source = preg_replace_callback("#\{%\s*endifblock\s*%\}#", function($m) use ($tagname, &$set_id_stack) {
			$set_id = array_shift($set_id_stack);
			$new = "{%- endset -%}{% if block('$tagname')|trim %}{{ $set_id }}{% endif %}";
			return $new;
		}, $source);

		return $source;
	}


	/**
	 * Processes self-closing tags as flags
	 *
	 * @param string $source
	 * @return string
	 */
	public function processSetFlags($source)
	{
		if (preg_match('#<dp:is-noreply\s*/>\s*#s', $source)) {
			$source = '{% set is_noreply = true %}' . $source;
			$source = preg_replace('#<dp:is-noreply\s*/>#', '', $source);
		}

		return $source;
	}


	/**
	 * Get a unique varname to use
	 *
	 * @return string
	 */
	public function getSetId()
	{
		static $id = 0;
		$id++;

		return 'set_' . time() . '_' . $id;
	}


	/**
	 * Get default code to prepend to the header of the template
	 *
	 * @return string
	 */
	public function getPrepend()
	{
		return <<<'SRC'
{% extends 'DeskPRO:emails_common:layout.html.twig' %}
{% import 'DeskPRO:emails_common:layout-macros.html.twig' as layout %}
SRC;
	}
}