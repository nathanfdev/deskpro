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

/**
 * This keeps a map of which fields should be applied to a specific type of resource.
 * Fields are inherently "global" in the system and can apply to anything, but many
 * times it only makes sense that a field be applied to some specific thing (ie users, tickets).
 *
 * @Entity(repositoryClass="Application\CoreBundle\EntityRepository\FormFieldAssociation")
 * @Table(name="form_field_associations")
 */
class FormFieldAssociation extends \DeskPRO\Domain\DomainObject
{
	const SYSTYPE_PERSON = 'person';

	/**
	 * The form field
	 * 
	 * @var int
	 * @Column(name="form_field_id", type="integer")
	 */
	protected $form_field_id;

	/**
	 * @var Application\CoreBundle\FormField
	 * @OneToOne(targetEntity="FormField")
	 * @JoinColumn(name="form_field_id", referencedColumnName="id")
	 */
	protected $form_field;

	/**
	 * The system type codename for the association.
	 *
	 * @var string
	 * @Column(name="systype", type="string", length=255)
	 */
	protected $systype;
}