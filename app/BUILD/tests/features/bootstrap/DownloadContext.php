<?php

namespace DpBehat;

use Application\DeskPRO\Entity\Download;
use DpBehat\Data\DataContext;

class DownloadContext extends BaseContext
{
    /**
     * @Then the :downloadId download should have properly tagged attachment
     *
     * @param int $messageId
     * @param int $count
     *
     * @throws \Exception
     */
    public function downloadHasProperlyTaggedAttachment($downloadId)
    {
        $downloadId = DataContext::replace($downloadId);
        $download   = $this->repository(Download::class)->find($downloadId);
        if (!$download) {
            throw new \Exception("Can't find download: $downloadId");
        }

        if (!$download->getBlob()->isDownloadAttachment()) {
            throw new \Exception("Blob with auth code ({$download->getBlob()->getAuthcode()}) not tagged as download attachment");
        }
    }
}
