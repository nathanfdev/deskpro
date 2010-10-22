<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\CoreBundle\Entity;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A PersonField describes a type of field that is set on a Person, and how the data
 * is to be treated/inputted/transformed etc.
 *
 * @Entity(repositoryClass="Application\CoreBundle\EntityRepository\PersonField")
 * @HasLifecycleCallbacks
 * @Table(name="person_fields")
 */
class PersonField extends FormField
{

}