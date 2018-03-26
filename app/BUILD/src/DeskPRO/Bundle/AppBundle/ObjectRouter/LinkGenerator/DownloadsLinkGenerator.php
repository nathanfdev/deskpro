<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGenerator;

use Application\DeskPRO\Entity\ArticleAttachment;
use Application\DeskPRO\Entity\Download;
use DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Creates the proper link for a download "serve" (a direct url to put in an img tag for ex.)
 * This is because the normal "save" type will first hit a controller
 * for security and to increment count + redirect
 * this avoids both security and the download increment, so be careful with the "serve" type on Downloads!
 */
class DownloadsLinkGenerator implements LinkGeneratorInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $url_generator;

    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface $url_generator
     */
    public function __construct(UrlGeneratorInterface $url_generator)
    {
        $this->url_generator = $url_generator;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, $type, $context)
    {
        return
            ($object instanceof Download && $type === 'serve')
            ||
            ($object instanceof ArticleAttachment && $type === 'serve')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($object, $type, $context, $extra_params, $reference_type)
    {
        if ($object instanceof Download && $object->getFileurl()) {
            return $object->getFileurl();
        }

        /* @var \Application\DeskPRO\Entity\ArticleAttachment|\Application\DeskPRO\Entity\Download $object */
        $blob = $object->getBlob();

        if (!$blob) {
            return '';
        }

        return $this->url_generator->generate(
            'serve_blob',
            array_merge([
                'blob_auth_id' => $blob->getAuthcode(),
                'filename'     => $blob->getFilenameSafe(),
            ], $extra_params),
            $reference_type
        );
    }
}
