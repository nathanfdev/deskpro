<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Storage;

use EWSType_BodyTypeResponseType;
use EWSType_ConstantValueType;
use EWSType_CreateFolderType;
use EWSType_DefaultShapeNamesType;
use EWSType_DeleteItemType;
use EWSType_DisposalType;
use EWSType_DistinguishedFolderIdNameType;
use EWSType_DistinguishedFolderIdType;
use EWSType_FieldOrderType;
use EWSType_FieldURIOrConstantType;
use EWSType_FindFolderType;
use EWSType_FindItemType;
use EWSType_FolderQueryTraversalType;
use EWSType_FolderResponseShapeType;
use EWSType_FolderType;
use EWSType_GetItemType;
use EWSType_IndexedPageViewType;
use EWSType_IsEqualToType;
use EWSType_ItemChangeType;
use EWSType_ItemIdType;
use EWSType_ItemQueryTraversalType;
use EWSType_ItemResponseShapeType;
use EWSType_MessageType;
use EWSType_MoveItemType;
use EWSType_NonEmptyArrayOfBaseFolderIdsType;
use EWSType_NonEmptyArrayOfBaseItemIdsType;
use EWSType_NonEmptyArrayOfFieldOrdersType;
use EWSType_NonEmptyArrayOfFoldersType;
use EWSType_NonEmptyArrayOfItemChangeDescriptionsType;
use EWSType_NonEmptyArrayOfPathsToElementType;
use EWSType_PathToUnindexedFieldType;
use EWSType_RestrictionType;
use EWSType_SetItemFieldType;
use EWSType_SortDirectionType;
use EWSType_UnindexedFieldURIType;
use EWSType_UpdateItemType;
use Orb\Util\Arrays;

class Exchange
{
    /**
     * @var \ExchangeWebServices
     */
    protected $service;

    /**
     * @var array
     */
    protected $folders;

    /**
     * @var \Orb\Log\Logger|null
     */
    protected $logger;

    public function __construct($options = [])
    {
        if (!isset($options['host']) ||
            !isset($options['user']) ||
            !isset($options['password'])
        ) {
            throw new \Exception('Insufficient Parameters');
        }

        if (isset($options['logger'])) {
            $this->logger = $options['logger'];
        }

        $this->service = new \ExchangeWebServices(
            $options['host'].(!empty($options['port']) && $options['port'] != 443 ? ":{$options['port']}" : ''),
            $options['user'],
            $options['password']
        );
    }

    /**
     * Creates a folder if it doesnt exist.
     *
     * @param string $name
     *
     * @return bool True if it was created, false otherwise
     */
    public function ensureFolderExists($name)
    {
        if (!$this->findFolder($name)) {
            $this->createFolder($name);

            return true;
        }

        return false;
    }

    /**
     * @param int  $limit
     * @param bool $unread_only
     * @param null $folder
     *
     * @return mixed
     */
    public function searchIds($limit = 10, $unread_only = false, $folder = null)
    {
        $request                   = new EWSType_FindItemType();
        $itemProperties            = new EWSType_ItemResponseShapeType();
        $itemProperties->BaseShape = EWSType_DefaultShapeNamesType::ID_ONLY;
        $itemProperties->BodyType  = EWSType_BodyTypeResponseType::BEST;
        $request->ItemShape        = $itemProperties;

        if ($unread_only) {
            $fieldType           = new EWSType_PathToUnindexedFieldType();
            $fieldType->FieldURI = 'message:IsRead';

            $constant                  = new EWSType_FieldURIOrConstantType();
            $constant->Constant        = new EWSType_ConstantValueType();
            $constant->Constant->Value = '0';

            $IsEqTo                     = new EWSType_IsEqualToType();
            $IsEqTo->FieldURIOrConstant = $constant;
            $IsEqTo->Path               = $fieldType;

            $request->Restriction                                = new EWSType_RestrictionType();
            $request->Restriction->IsEqualTo                     = new EWSType_IsEqualToType();
            $request->Restriction->IsEqualTo->FieldURI           = $fieldType;
            $request->Restriction->IsEqualTo->FieldURIOrConstant = $constant;
        }

        $request->IndexedPageItemView                     = new EWSType_IndexedPageViewType();
        $request->IndexedPageItemView->BasePoint          = 'Beginning';
        $request->IndexedPageItemView->Offset             = 0;
        $request->IndexedPageItemView->MaxEntriesReturned = $limit; // Number of items to return in total

        if (!$folder) {
            $request->ParentFolderIds                            = new EWSType_NonEmptyArrayOfBaseFolderIdsType();
            $request->ParentFolderIds->DistinguishedFolderId     = new EWSType_DistinguishedFolderIdType();
            $request->ParentFolderIds->DistinguishedFolderId->Id = EWSType_DistinguishedFolderIdNameType::INBOX;
        } else {
            $folder = $this->findFolder($folder);
            if ($folder) {
                $request->ParentFolderIds               = new EWSType_NonEmptyArrayOfBaseFolderIdsType();
                $request->ParentFolderIds->FolderId     = new \EWSType_FolderIdType();
                $request->ParentFolderIds->FolderId->Id = $folder->FolderId->Id;
            }
        }

        $order                          = new EWSType_FieldOrderType();
        $order->FieldURI                = new EWSType_PathToUnindexedFieldType();
        $order->FieldURI->FieldURI      = EWSType_UnindexedFieldURIType::ITEM_DATE_TIME_RECEIVED;
        $order->Order                   = EWSType_SortDirectionType::ASCENDING;
        $request->SortOrder             = new EWSType_NonEmptyArrayOfFieldOrdersType();
        $request->SortOrder->FieldOrder = [$order];

        $request->SortOrder = $order;
        $request->Traversal = EWSType_ItemQueryTraversalType::SHALLOW;

        $response = $this->service->FindItem($request);

        if ($response->ResponseMessages->FindItemResponseMessage->ResponseCode == 'NoError' &&
            $response->ResponseMessages->FindItemResponseMessage->ResponseClass == 'Success'
        ) {
            $ids = [];

            if (!isset($response->ResponseMessages->FindItemResponseMessage->RootFolder->Items->Message) ||
            empty($response->ResponseMessages->FindItemResponseMessage->RootFolder->Items->Message)) {
                return $ids;
            }

            foreach ($response->ResponseMessages->FindItemResponseMessage->RootFolder->Items->Message as $m) {
                if (isset($m->Id)) {
                    $ids[] = $m->Id;
                } elseif (isset($m->ItemId->Id)) {
                    $ids[] = $m->ItemId->Id;
                }
            }
            $ids = Arrays::removeFalsey($ids);

            return $ids;
        }

        return [];
    }

    /**
     * @param string $message_id
     *
     * @return string
     */
    public function getRawMessage($message_id)
    {
        $request = new EWSType_GetItemType();

        $request->ItemShape                     = new EWSType_ItemResponseShapeType();
        $request->ItemShape->BaseShape          = EWSType_DefaultShapeNamesType::ID_ONLY; // set to ID_ONLY so we can request individual items and lower bytes xferred
        $request->ItemShape->IncludeMimeContent = true;

        // set fields we want to request
        $subject             = new EWSType_PathToUnindexedFieldType();
        $subject->FieldURI   = 'item:Subject';
        $date                = new EWSType_PathToUnindexedFieldType();
        $date->FieldURI      = 'item:DateTimeReceived';
        $messageId           = new EWSType_PathToUnindexedFieldType();
        $messageId->FieldURI = 'message:InternetMessageId';
        $isRead              = new EWSType_PathToUnindexedFieldType();
        $isRead->FieldURI    = 'message:IsRead';

        $request->ItemShape->AdditionalProperties           = new EWSType_NonEmptyArrayOfPathsToElementType();
        $request->ItemShape->AdditionalProperties->FieldURI = [$subject, $date, $messageId, $isRead];

        $request->ItemIds             = new EWSType_NonEmptyArrayOfBaseItemIdsType();
        $request->ItemIds->ItemId     = new EWSType_ItemIdType();
        $request->ItemIds->ItemId->Id = $message_id;

        $response = $this->service->GetItem($request);

        if ($response && $response->ResponseMessages->GetItemResponseMessage->ResponseCode == 'NoError' &&
            $response->ResponseMessages->GetItemResponseMessage->ResponseClass == 'Success'
        ) {
            return base64_decode($response->ResponseMessages->GetItemResponseMessage->Items->Message->MimeContent->_);
        }

        return;
    }

    /**
     * @param string $message_id
     *
     * @return string
     */
    public function getRawHeaders($message_id)
    {
        $source = $this->getRawMessage($message_id);

        $pos = strpos($source, "\r\n\r\n");
        if ($pos === false) {
            $pos = strpos($source, "\n\n");
        }
        if ($pos === false) {
            return '';
        }

        $rawHeader = substr($source, 0, $pos);

        return $rawHeader;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    private function createFolder($name)
    {
        $request                                            = new EWSType_CreateFolderType();
        $request->Folders                                   = new EWSType_NonEmptyArrayOfFoldersType();
        $request->Folders->Folder                           = new EWSType_FolderType();
        $request->Folders->Folder->DisplayName              = $name;
        $request->ParentFolderId                            = new EWSType_NonEmptyArrayOfBaseFolderIdsType();
        $request->ParentFolderId->DistinguishedFolderId     = new \stdClass();
        $request->ParentFolderId->DistinguishedFolderId->Id = EWSType_DistinguishedFolderIdNameType::MESSAGE_ROOT;

        $response = $this->service->CreateFolder($request);

        if ($response->ResponseMessages->CreateFolderResponseMessage->ResponseCode == 'NoError' &&
            $response->ResponseMessages->CreateFolderResponseMessage->ResponseClass == 'Success'
        ) {
            return $response->ResponseMessages->CreateFolderResponseMessage->Folders->Folder->FolderId;
        }
    }

    /**
     * @param string $name
     * @param bool   $force_reload
     *
     * @return mixed
     */
    private function findFolder($name, $force_reload = false)
    {
        if ($force_reload || !$this->folders) {
            $this->folders = $this->getFolderList();
        }

        foreach ($this->folders as $folder) {
            if (strtolower($name) === strtolower($folder->DisplayName)) {
                return $folder;
            }
        }

        return;
    }

    /**
     * @return mixed
     */
    private function getFolderList()
    {
        $request = new EWSType_FindFolderType();

        $request->Traversal              = EWSType_FolderQueryTraversalType::SHALLOW; // use EWSType_FolderQueryTraversalType::DEEP for subfolders too
        $request->FolderShape            = new EWSType_FolderResponseShapeType();
        $request->FolderShape->BaseShape = EWSType_DefaultShapeNamesType::ALL_PROPERTIES;

        // configure the view
        $request->IndexedPageFolderView            = new EWSType_IndexedPageViewType();
        $request->IndexedPageFolderView->BasePoint = 'Beginning';
        $request->IndexedPageFolderView->Offset    = 0;

        $request->ParentFolderIds = new EWSType_NonEmptyArrayOfBaseFolderIdsType();

        // use a distinguished folder name to find folders inside it
        $request->ParentFolderIds->DistinguishedFolderId     = new EWSType_DistinguishedFolderIdType();
        $request->ParentFolderIds->DistinguishedFolderId->Id = EWSType_DistinguishedFolderIdNameType::MESSAGE_ROOT;

        // request
        $response = $this->service->FindFolder($request);

        if ($response && $response->ResponseMessages->FindFolderResponseMessage->ResponseCode == 'NoError' &&
            $response->ResponseMessages->FindFolderResponseMessage->ResponseClass == 'Success'
        ) {
            return $response->ResponseMessages->FindFolderResponseMessage->RootFolder->Folders->Folder;
        } else {
            return [];
        }
    }

    /**
     * @param $message
     *
     * @return bool
     */
    public function moveMessage($message_id, $name)
    {
        $folder = $this->findFolder($name);

        $request = new EWSType_MoveItemType();

        @$request->ToFolderId->FolderId->Id        = $folder->FolderId->Id;
        @$request->ToFolderId->FolderId->ChangeKey = $folder->FolderId->ChangeKey;
        @$request->ItemIds->ItemId->Id             = $message_id;

        // Generic execution sample code
        $response = $this->service->MoveItem($request);

        if ($response && $response->ResponseMessages->MoveItemResponseMessage->ResponseCode == 'NoError' &&
            $response->ResponseMessages->MoveItemResponseMessage->ResponseClass == 'Success'
        ) {
            return $response->ResponseMessages->MoveItemResponseMessage->Items->Message->ItemId;
        }

        return false;
    }

    /**
     * @param string $message_id
     *
     * @return bool
     */
    public function deleteMessage($message_id)
    {
        $request                      = new EWSType_DeleteItemType();
        $request->ItemIds             = new EWSType_NonEmptyArrayOfBaseItemIdsType();
        $request->ItemIds->ItemId     = new EWSType_ItemIdType();
        $request->ItemIds->ItemId->Id = $message_id;

        $request->DeleteType = new EWSType_DisposalType();
        $request->DeleteType = EWSType_DisposalType::MOVE_TO_DELETED_ITEMS;

        $response = $this->service->DeleteItem($request);

        if ($response && $response->ResponseMessages->DeleteItemResponseMessage->ResponseCode == 'NoError' &&
            $response->ResponseMessages->DeleteItemResponseMessage->ResponseClass == 'Success'
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param $message_id
     *
     * @return bool
     */
    public function markRead($message_id)
    {
        $request                     = new EWSType_UpdateItemType();
        $request->ConflictResolution = 'AlwaysOverwrite';
        $request->MessageDisposition = 'SaveOnly';
        $request->ItemChanges        = [];

        $change                    = new EWSType_ItemChangeType();
        $change->ItemId            = new EWSType_ItemIdType();
        $change->ItemId->Id        = $message_id;
        $change->ItemId->ChangeKey = $this->getChangeKey($message_id);

        $field                     = new EWSType_SetItemFieldType();
        $field->FieldURI           = new EWSType_PathToUnindexedFieldType();
        $field->FieldURI->FieldURI = 'message:IsRead';
        $field->Message            = new EWSType_MessageType();
        $field->Message->IsRead    = true;

        $change->Updates                 = new EWSType_NonEmptyArrayOfItemChangeDescriptionsType();
        $change->Updates->SetItemField   = [];
        $change->Updates->SetItemField[] = $field;

        $request->ItemChanges[] = $change;

        $response = $this->service->UpdateItem($request);

        if ($response && $response->ResponseMessages->UpdateItemResponseMessage->ResponseCode == 'NoError' &&
            $response->ResponseMessages->UpdateItemResponseMessage->ResponseClass == 'Success'
        ) {
            return true;
        }

        return false;
    }

    /**
     * Gets email properties.
     *
     * @param string $message_id
     *
     * @return mixed
     */
    public function getEmailProps($message_id)
    {
        if ($this->logger) {
            $this->logger->logDebug("Reading message: $message_id");
        }

        // Build the request for the parts.
        $request                       = new EWSType_GetItemType();
        $request->ItemShape            = new EWSType_ItemResponseShapeType();
        $request->ItemShape->BaseShape = EWSType_DefaultShapeNamesType::ALL_PROPERTIES;
        // You can get the body as HTML, text or "best".
        $request->ItemShape->BodyType = EWSType_BodyTypeResponseType::HTML;

        // Add the body property.
        $body_property                                      = new EWSType_PathToUnindexedFieldType();
        $body_property->FieldURI                            = 'item:Body';
        $request->ItemShape->AdditionalProperties           = new EWSType_NonEmptyArrayOfPathsToElementType();
        $request->ItemShape->AdditionalProperties->FieldURI = [$body_property];

        $request->ItemIds         = new EWSType_NonEmptyArrayOfBaseItemIdsType();
        $request->ItemIds->ItemId = [];

        // Add the message to the request.
        $message_item               = new EWSType_ItemIdType();
        $message_item->Id           = $message_id;
        $request->ItemIds->ItemId[] = $message_item;

        $response = $this->service->GetItem($request);

        $reason = 'unknown';
        if (!empty($response->ResponseMessages->GetItemResponseMessage->ResponseCode)) {
            $reason = $response->ResponseMessages->GetItemResponseMessage->ResponseCode.': ';
        }
        if (!empty($response->ResponseMessages->GetItemResponseMessage->MessageText)) {
            $reason .= $response->ResponseMessages->GetItemResponseMessage->MessageText;
        }

        if ($response->ResponseMessages->GetItemResponseMessage->ResponseCode == 'NoError' &&
            $response->ResponseMessages->GetItemResponseMessage->ResponseClass == 'Success') {
            if ($this->logger) {
                $this->logger->logDebug("Success: $reason");
            }

            return $response->ResponseMessages->GetItemResponseMessage->Items->Message;
        }

        if ($this->logger) {
            $this->logger->logDebug("Failure: $reason");
        }
        throw new \UnexpectedValueException('Failed to read message: '.$reason);
    }

    protected function getChangeKey($message_id)
    {
        $message = $this->getEmailProps($message_id);

        return @$message->ItemId->ChangeKey;
    }

    public function close()
    {
        return true;
    }
}
