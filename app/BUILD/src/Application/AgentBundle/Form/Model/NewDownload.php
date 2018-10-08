<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Form\Model;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Person;
use Orb\Util\Web;

class NewDownload
{
    /** @var string */
    public $title = '';
    /** @var int */
    public $category_id;
    /** @var string */
    public $status;
    /** @var string */
    public $content = '';

    /** @var string|null */
    public $fileurl = null;
    /** @var string|null */
    public $filename = null;
    /** @var int|null */
    public $filesize = null;

    /** @var string */
    public $slug;
    /** @var string */
    public $labels_json;
    /** @var array */
    public $labels = [];

    /** @var int|null */
    public $attach = null;

    /** @var int[] */
    public $blob_inline_ids = [];

    /** @var Download */
    protected $_download;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $_em;

    public function __construct(Person $person_context)
    {
        $this->_person_context = $person_context;

        $this->_em = App::getOrm();
    }

    public function save()
    {
        $this->_em->beginTransaction();

        $download         = new Download();
        $download->person = $this->_person_context;
        $download->title  = $this->title;

        $download->content = $this->_person_context->hasPerm('agent_publish.can_insert_html')
            ? App::$container->getInputCleaner()->clean($this->content ?: '', 'string', ['noclean' => true])
            : App::$container->getInputCleaner()->clean($this->content ?: '', 'html');

        $download->setStatusCode($this->status);

        if ($download->getStatusCode() == 'published' && !$this->_person_context->hasPerm('agent_publish.validate')) {
            $download->setStatusCode('hidden.unpublished');
        }

        $cat                = $this->_em->find('DeskPRO:DownloadCategory', $this->category_id);
        $download->category = $cat;

        if ($this->attach) {
            /** @var Blob $blob */
            $blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($this->attach);
            $blob->setIsTemp(false);
            $download->setBlob($blob);
            $download->setFilename($blob->getFilename());

            if (!$download->getTitle()) {
                $download->setTitle($blob->getFilename());
            }

            $this->_em->persist($blob);
        } else {
            $fileurl  = $this->fileurl;
            $filesize = $this->filesize;
            $filename = $this->filename;

            if (!$filename) {
                $filename = Web::getUrlFileName($fileurl);
                if (!$filename) {
                    $filename = '';
                }
            }
            if (!$filesize) {
                $filesize = Web::getUrlFileSize($fileurl);
                if (!$filesize) {
                    $filesize = 0;
                }
            }

            $download->setFileUrl(
                $fileurl,
                $filesize,
                $filename
            );

            if (!$download->title) {
                $download->title = $download->getFileName();
            }
        }

        if ($this->blob_inline_ids) {
            $inlineBlobs = $this->_em->getRepository(Blob::class)->findBy(['id' => $this->blob_inline_ids]);
            foreach ($inlineBlobs as $blob) {
                $this->_em->persist($blob->setIsTemp(false));
            }
        }

        $this->_em->persist($download);
        $this->_em->flush();

        if ($this->labels) {
            $download->getLabelManager()->setLabelsArray($this->labels, $this->_em);
        }

        $this->_em->flush();
        $this->_em->commit();

        $this->_download = $download;
    }

    public function getDownload()
    {
        return $this->_download;
    }
}
