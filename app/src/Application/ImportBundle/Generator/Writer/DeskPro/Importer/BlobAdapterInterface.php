<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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

namespace Application\ImportBundle\Generator\Writer\DeskPro\Importer;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;

/**
 * Blob storage adapter interface
 *
 * Interface BlobAdapterInterface
 * @package Application\ImportBundle\Generator\Writer\DeskPro\Importer
 */
interface BlobAdapterInterface
{
    /**
     * Creates a blob object
     *
     * @param string $source_data
     * @param string $filename
     * @param string $content_type
     *
     * @return DeskPROEntity\Blob
     */
    public function createBySourceData($source_data, $filename, $content_type);

    /**
     * Creates a blob object by an importer attachment entity
     *
     * @param Entity\Attachment $attachment
     * @return DeskPROEntity\Blob
     */
    public function createByAttachment(Entity\Attachment $attachment);
}
