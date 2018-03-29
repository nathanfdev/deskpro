<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Download;
use JMS\Serializer\Annotation as JMS;

class DownloadSubscription extends UserEmailBaseType
{
    /**
     * The new downloads.
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Download>")
     *
     * @var Download[]
     */
    protected $newDownloads;

    /**
     * The updated downloads.
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Download>")
     *
     * @var Download[]
     */
    protected $updatedDownloads;

    /**
     * Link to unsubscribe to downloads.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $unsubscribeUrl;

    protected $templateFile = 'emails_user:download_subscription.html.twig';

    /**
     * DownloadSubscription constructor.
     *
     * @param string $portalHome
     * @param $unsubscribeUrl
     * @param Download[] $newDownloads
     * @param Download[] $updatedDownloads
     */
    public function __construct($portalHome, $unsubscribeUrl, $newDownloads, $updatedDownloads)
    {
        parent::__construct($portalHome);

        $this->newDownloads     = $newDownloads;
        $this->updatedDownloads = $updatedDownloads;
        $this->unsubscribeUrl   = $unsubscribeUrl;
    }
}
