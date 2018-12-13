<?php

namespace DeskPRO\Bundle\AppBundle\Helper;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\EntityRepository\Blob as BlobRepository;
use DeskPRO\Component\Util\MatchConfig;
use DeskPRO\Component\Util\StringUtils;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\RouterInterface;

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
     * @var RouterInterface
     */
    private $router;

    /**
     * AttachmentHelper constructor.
     *
     * @param EntityManager   $em
     * @param RouterInterface $router
     */
    public function __construct(EntityManager $em, RouterInterface $router)
    {
        $this->em     = $em;
        $this->router = $router;
    }

    /**
     * @param string $content
     * @param array  $blobIds
     *
     * @return Blob[]
     */
    public function processInlineBlobs($content, $blobIds = [])
    {
        $matchConfig = new MatchConfig(
            $this->router->generate('serve_blob', ['blob_auth_id' => '00000', 'filename' => '11111']),
            '00000',
            '11111'
        );
        $blobAuthcodes = StringUtils::gatherInlineAttachments($content, $matchConfig);
        /** @var BlobRepository $blobRepository */
        $blobRepository = $this->em->getRepository(Blob::class);
        // these we found via html
        $allBlobs = $inlineBlobsInMessage = $blobRepository->getByAuthCodes($blobAuthcodes) ?: [];
        foreach ($inlineBlobsInMessage as $blob) {
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
            $allBlobs = array_merge($allBlobs, $inlineBlobs);
        }

        return array_filter($allBlobs, function (Blob $blob) {
            return !$blob->isTemp();
        });
    }

    /**
     * @param $entity
     */
    public function verifyBlobs($entity)
    {
        $content = null;
        if ($entity instanceof TicketMessage) {
            $content = $entity->getMessageHtml();
        } elseif ($entity instanceof ContentAbstract) {
            $content = $entity->getContentHtml();
        }

        if ($content) {
            $this->processInlineBlobs($content);
        }
    }
}
