<?php

namespace DeskPRO\Bundle\AppBundle\Helper;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\EntityRepository\Blob as BlobRepository;
use DeskPRO\Component\Util\StringUtils;
use Doctrine\ORM\EntityManager;

/**
 * Class AttachmentHelper
 * used to check attachment in html content.
 */
class AttachmentHelper
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * AttachmentHelper constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $content
     * @param array  $blobIds
     */
    public function processInlineBlobs($content, $blobIds = [])
    {
        $blobAuthcodes = StringUtils::gatherInlineAttachments($content);
        /** @var BlobRepository $blobRepository */
        $blobRepository = $this->em->getRepository(Blob::class);
        // these we found via html
        $inlineBlobs = $blobRepository->getByAuthCodes($blobAuthcodes);
        foreach ($inlineBlobs as $blob) {
            $this->em->persist($blob->setIsTemp(false));
        }

        // Message Attachments
        if ($blobIds) {
            $inlineBlobs = $blobRepository->findBy(['id' => $blobIds]);
            foreach ($inlineBlobs as $blob) {
                /** @var Blob $blob */
                if (StringUtils::ensureAttachment($blob, $content)) {
                    $this->em->persist($blob->setIsTemp(false));
                }
            }
        }
    }
}
