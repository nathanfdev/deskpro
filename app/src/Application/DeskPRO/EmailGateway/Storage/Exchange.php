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
 * @subpackage EmailGateway
 */

namespace Application\DeskPRO\EmailGateway\Storage;

use ExchangeWebServices;
use EWSType_FindItemType;
use EWSType_FindFolderType;
use EWSType_FolderQueryTraversalType;
use EWSType_FolderResponseShapeType;
use EWSType_ItemResponseShapeType;
use EWSType_ItemQueryTraversalType;
use EWSType_IndexedPageViewType;
use EWSType_DefaultShapeNamesType;
use EWSType_NonEmptyArrayOfBaseFolderIdsType;
use EWSType_DistinguishedFolderIdType;
use EWSType_DistinguishedFolderIdNameType;
use EWSType_NonEmptyArrayOfFieldOrdersType;
use EWSType_NonEmptyArrayOfBaseItemIdsType;
use EWSType_CreateFolderType;
use EWSType_FolderType;
use EWSType_BodyTypeResponseType;
use EWSType_PathToUnindexedFieldType;
use EWSType_FieldURIOrConstantType;
use EWSType_ConstantValueType;
use EWSType_IsEqualToType;
use EWSType_RestrictionType;
use EWSType_FieldOrderType;
use EWSType_FindItemResponseMessageType;

class Exchange
{
	/**
	 * The defualt mode
	 *
	 * It makes the message "READ" after processing it
	 */
	const MODE_DEFAULT		= 1;

	/**
	 * The aggresive mode
	 *
	 * Processes the message and then immediately deletes it
	 */
	const MODE_AGGRESIVE	= 2;

	/**
	 * The preserving mode
	 *
	 * It preserves all the messages, after processinga message moves it to a
	 * specified folder
	 */
	const MODE_PRESERVE		= 3;

	protected $service;

	protected $folders;

	protected $mode = self::MODE_DEFAULT;

	protected $dbFolderName;

	public function __construct($server, $username, $password, $mode = self::MODE_DEFAULT, $dpFolderName = null)
	{
		$this->service = new \ExchangeWebServices($server, $username, $password);

		$this->mode = $mode;

		if (self::MODE_PRESERVE === $this->mode) {
			if (!$dpFolderName) {
				throw new \Exception("In preservative mode fetching you must provide a folder name");
			}
			if (!$this->findFolder($dpFolderName)) {
				$this->createFolder($dpFolderName);
			}

			$this->dbFolderName = $dpFolderName;
		}
	}

	public function searchIds($limit)
	{
		$request = new EWSType_FindItemType();
		$itemProperties = new EWSType_ItemResponseShapeType();
		$itemProperties->BaseShape = EWSType_DefaultShapeNamesType::ID_ONLY;
		$itemProperties->BodyType = EWSType_BodyTypeResponseType::BEST;
		$request->ItemShape = $itemProperties;

		if (self::MODE_DEFAULT === $this->mode) {
			$fieldType = new EWSType_PathToUnindexedFieldType();
			$fieldType->FieldURI = 'message:IsRead';

			$constant = new EWSType_FieldURIOrConstantType();
			$constant->Constant = new EWSType_ConstantValueType();
			$constant->Constant->Value = "0";

			$IsEqTo = new EWSType_IsEqualToType();
			$IsEqTo->FieldURIOrConstant = $constant;
			$IsEqTo->Path = $fieldType;

			$request->Restriction = new EWSType_RestrictionType();
			$request->Restriction->IsEqualTo = new EWSType_IsEqualToType();
			$request->Restriction->IsEqualTo->FieldURI = $fieldType;
			$request->Restriction->IsEqualTo->FieldURIOrConstant = $constant;
		}

		$request->IndexedPageItemView = new EWSType_IndexedPageViewType();
		$request->IndexedPageItemView->BasePoint = 'Beginning';
		$request->IndexedPageItemView->Offset = 0;
		$request->IndexedPageItemView->MaxEntriesReturned = $limit; // Number of items to return in total

		$request->ParentFolderIds = new EWSType_NonEmptyArrayOfBaseFolderIdsType();
		$request->ParentFolderIds->DistinguishedFolderId = new EWSType_DistinguishedFolderIdType();
		$request->ParentFolderIds->DistinguishedFolderId->Id = EWSType_DistinguishedFolderIdNameType::INBOX;

		$request->Traversal = EWSType_ItemQueryTraversalType::SHALLOW;

		$response = $this->service->FindItem($request);

		if ($response->ResponseMessages->FindItemResponseMessage->ResponseCode == 'NoError' &&
			$response->ResponseMessages->FindItemResponseMessage->ResponseClass == 'Success') {

			return @$response->ResponseMessages->FindItemResponseMessage->RootFolder->Items->Message;
		}

	}

	public function getRawMessage($message)
	{
		$request = new EWSType_GetItemType();

		$request->ItemShape = new EWSType_ItemResponseShapeType();
		$request->ItemShape->BaseShape = EWSType_DefaultShapeNamesType::ID_ONLY;  // set to ID_ONLY so we can request individual items and lower bytes xferred
		$request->ItemShape->IncludeMimeContent = true;

		// set fields we want to request
		$subject = new EWSType_PathToUnindexedFieldType();
		$subject->FieldURI = 'item:Subject';
		$date = new EWSType_PathToUnindexedFieldType();
		$date->FieldURI = 'item:DateTimeReceived';
		$messageId = new EWSType_PathToUnindexedFieldType();
		$messageId->FieldURI = 'message:InternetMessageId';
		$isRead = new EWSType_PathToUnindexedFieldType();
		$isRead->FieldURI = 'message:IsRead';

		$request->ItemShape->AdditionalProperties = new EWSType_NonEmptyArrayOfPathsToElementType();
		$request->ItemShape->AdditionalProperties->FieldURI = array($subject, $date, $messageId, $isRead);

		$request->ItemIds = new EWSType_NonEmptyArrayOfBaseItemIdsType();
		$request->ItemIds->ItemId = new EWSType_ItemIdType();
		$request->ItemIds->ItemId->Id = $message->Id;

		$response = $this->service->GetItem($request);

		if ($response->ResponseMessages->GetItemResponseMessage->ResponseCode == 'NoError' &&
			$response->ResponseMessages->GetItemResponseMessage->ResponseClass == 'Success') {

			return base64_decode($response->ResponseMessages->GetItemResponseMessage->Items->Message->MimeContent->_);
		}
	}

	public function getRawHeaders($message)
	{
		$rawHeader = '';

		foreach($this->getEmailParts($message)->InternetMessageHeaders->InternetMessageHeader as $header) {
			$rawHeader .= $header->HeaderName . ':' . $header->_ . PHP_EOL;
		}

		return $rawHeader;
	}

	public function createFolder($name)
	{
		$request = new EWSType_CreateFolderType();
		$request->Folders = new EWSType_NonEmptyArrayOfBaseFolderIdsType();
		$request->Folders->Folder = new EWSType_FolderType();
		$request->Folders->Folder->DisplayName = $name;
		$request->Folders->Folder->FolderClass = 'IPF.Note';
		$request->ParentFolderId = new EWSType_NonEmptyArrayOfBaseFolderIdsType();
		@$request->ParentFolderId->FolderId->Id = 'AQASAGFiaGluYXZAZGVza3Byby50dgAuAAADPCjZ3ICjxEWl5qAA1fTmKAEAk8LZ7dAm+Eui4eBNqUbngAAAAX36HwAAAA==';
		//$request->ParentFolderId->DistinguishedFolderId = new EWSType_DistinguishedFolderIdType();
		//$request->ParentFolderId->DistinguishedFolderId->Id = EWSType_DistinguishedFolderIdNameType::INBOX;

		$response = $this->service->CreateFolder($request);

		if ($response->ResponseMessages->CreateFolderResponseMessage->ResponseCode == 'NoError' &&
			$response->ResponseMessages->CreateFolderResponseMessage->ResponseClass == 'Success') {
			return $response->ResponseMessages->CreateFolderResponseMessage->Folders->Folder->FolderId;
		}
	}

	public function findFolder($name, $forceReload = false)
	{
		if ($forceReload || !$this->folders) {
			$this->folders = $this->getFolderList();
		}

		foreach ($this->folders as $folder) {
			if (strtolower($name) === strtolower($folder->DisplayName)) {
				return $folder;
			}
		}
	}

	public function getFolderList()
	{
		$request = new EWSType_FindFolderType();

		$request->Traversal = EWSType_FolderQueryTraversalType::SHALLOW; // use EWSType_FolderQueryTraversalType::DEEP for subfolders too
		$request->FolderShape = new EWSType_FolderResponseShapeType();
		$request->FolderShape->BaseShape = EWSType_DefaultShapeNamesType::ALL_PROPERTIES;

		// configure the view
		$request->IndexedPageFolderView = new EWSType_IndexedPageViewType();
		$request->IndexedPageFolderView->BasePoint = 'Beginning';
		$request->IndexedPageFolderView->Offset = 0;

		$request->ParentFolderIds = new EWSType_NonEmptyArrayOfBaseFolderIdsType();

		// use a distinguished folder name to find folders inside it
		$request->ParentFolderIds->DistinguishedFolderId = new EWSType_DistinguishedFolderIdType();
		$request->ParentFolderIds->DistinguishedFolderId->Id = EWSType_DistinguishedFolderIdNameType::MESSAGE_FOLDER_ROOT;

		// if you know exact folder id, then use this piece of code instead. For example
		// $folder_id = 'AAKkADE4N2NkZDRjLWZjY2EtNDNlFy04MjFlLTkzODAyXTMyMGVmOABGAAAAAACO4PBzuy...';
		// $request->ParentFolderIds->FolderId = new EWSType_FolderIdType();
		// $request->ParentFolderIds->FolderId->Id = $folder_id;

		// request
		$response = $this->service->FindFolder($request);

		if ($response->ResponseMessages->FindFolderResponseMessage->ResponseCode == 'NoError' &&
			$response->ResponseMessages->FindFolderResponseMessage->ResponseClass == 'Success') {
			return $response->ResponseMessages->FindFolderResponseMessage->RootFolder->Folders->Folder;
		}
	}

	public function moveMessage($message)
	{
		$folder = $this->findFolder($this->dbFolderName);

		$request = new EWSType_MoveItemType();

		@$request->ToFolderId->FolderId->Id = $folder->FolderId->Id;

		@$request->ToFolderId->FolderId->ChangeKey = $folder->FolderId->ChangeKey;

		@$request->ItemIds->ItemId->Id = $message->ItemId->Id;
		@$request->ItemIds->ItemId->ChangeKey = $message->ItemId->ChangeKey;

		// Generic execution sample code
		$response = $this->service->MoveItem($request);

		if ($response->ResponseMessages->MoveItemResponseMessage->ResponseCode == 'NoError' &&
			$response->ResponseMessages->MoveItemResponseMessage->ResponseClass == 'Success') {
			return $response->ResponseMessages->MoveItemResponseMessage->Items->Message->ItemId;
		}
	}

	public function deleteMessage($message)
	{
		$request = new EWSType_DeleteItemType();
		$request->ItemIds = new EWSType_NonEmptyArrayOfBaseItemIdsType();
		$request->ItemIds->ItemId = new EWSType_ItemIdType();
		$request->ItemIds->ItemId->Id = $message->ItemId->Id;

		$request->DeleteType = new EWSType_DisposalType();
		$request->DeleteType = EWSType_DisposalType::MOVE_TO_DELETED_ITEMS;

		$response = $this->service->DeleteItem($request);

		if ($response->ResponseMessages->DeleteItemResponseMessage->ResponseCode == 'NoError' &&
			$response->ResponseMessages->DeleteItemResponseMessage->ResponseClass == 'Success') {
			return true;
		}
	}

	public function getEmailParts($message)
	{
		$message_id = $message->ItemId->Id;

		// Build the request for the parts.
		$request = new EWSType_GetItemType();
		$request->ItemShape = new EWSType_ItemResponseShapeType();
		$request->ItemShape->BaseShape = EWSType_DefaultShapeNamesType::ALL_PROPERTIES;
		// You can get the body as HTML, text or "best".
		$request->ItemShape->BodyType = EWSType_BodyTypeResponseType::HTML;

		// Add the body property.
		$body_property = new EWSType_PathToUnindexedFieldType();
		$body_property->FieldURI = 'item:Body';
		$request->ItemShape->AdditionalProperties = new EWSType_NonEmptyArrayOfPathsToElementType();
		$request->ItemShape->AdditionalProperties->FieldURI = array($body_property);

		$request->ItemIds = new EWSType_NonEmptyArrayOfBaseItemIdsType();
		$request->ItemIds->ItemId = array();

		// Add the message to the request.
		$message_item = new EWSType_ItemIdType();
		$message_item->Id = $message_id;
		$request->ItemIds->ItemId[] = $message_item;

		$response = $this->service->GetItem($request);

		if ($response->ResponseMessages->GetItemResponseMessage->ResponseCode == 'NoError' &&
			$response->ResponseMessages->GetItemResponseMessage->ResponseClass == 'Success') {
			return $response->ResponseMessages->GetItemResponseMessage->Items->Message;
		}
	}
}