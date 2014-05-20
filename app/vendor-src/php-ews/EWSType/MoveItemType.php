<?php
/**
 * Definition of the MoveItemType type
 *
 * @package php-ews
 * @subpackage Types
 */

/**
 * Definition of the MoveItemType type
 *
 * THIS IS A DESKPRO FILE
 * Hacked in because it is not included by default.
 * See https://github.com/jamesiarmes/php-ews/issues/100
 */
class EWSType_MoveItemType extends EWSType
{
	/**
	 * ToFolderId property
	 *
	 * @var EWSType_TargetFolderIdType
	 */
	public $ToFolderId;

	/**
	 * DistinguishedFolderId property
	 *
	 * @var EWSType_DistinguishedFolderIdType
	 */
	public $DistinguishedFolderId;

	/**
	 * ItemIds property
	 *
	 * @var EWSType_NonEmptyArrayOfBaseItemIdsType
	 */
	public $ItemIds;
}