<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Elastica
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Elastica\Transformer;


/**
 * A transformer takes some value and transforms it into an Elastica_Document.
 * 
 * Note that the document shouldn't set an index or typename, the Type
 * classes set those.
 */
interface TransformerInterface
{
	public function transform($values);
}