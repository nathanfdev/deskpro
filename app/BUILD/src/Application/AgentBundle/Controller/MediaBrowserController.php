<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller;

/**
 * The mediabrowser does everything via ajax.
 */
class MediaBrowserController extends AbstractController
{
    //###########################################################################
    // accept-upload
    //###########################################################################

    public function acceptUploadAction()
    {
        $files = $this->request->files->get('files');

        $data = [];

        foreach ($files as $file) {
            /* @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */

            $blob = $this->container->getBlobStorage()->createBlobRecordFromFile(
                $file->getRealPath(),
                $file->getClientOriginalName(),
                $file->getClientMimeType()
            );
            $blob_id = $blob->getId();

            $data[] = [
                'blob_id'  => $blob_id,
                'is_image' => $blob->isImage(),
                'row_html' => $this->renderView('AgentBundle:MediaBrowser:file-row.html.twig', ['blob' => $blob]),
            ];
        }

        return $this->createJsonResponse($data);
    }

    //###########################################################################
    // image-editor
    //###########################################################################

    public function imageEditorAction($blob_id)
    {
        /** @var $blob \Application\DeskPRO\Entity\Blob */
        $blob = $this->em->find('DeskPRO:Blob', $blob_id);

        return $this->render('AgentBundle:MediaBrowser:image-editor.html.twig', ['blob' => $blob]);
    }

    public function saveImageEditorAction($blob_id)
    {
        /** @var $blob \Application\DeskPRO\Entity\Blob */
        $blob = $this->em->find('DeskPRO:Blob', $blob_id);

        $file = $this->container->getBlobStorage()->copyBlobRecordToString($blob);

        $im = new \Imagick();
        $im->readImageBlob($file, $blob['filename']);
        $im->cropimage($this->in->getInt('w'), $this->in->getInt('h'), $this->in->getInt('x'), $this->in->getInt('y'));

        $new_blob = $this->container->getBlobStorage()->createBlobRecordFromString(
            $im->getImageBlob(),
            $blob['filename'],
            $blob['content_type']
        );
        $new_blob_id               = $blob->getId();
        $new_blob['original_blob'] = $blob;

        $this->em->persist($new_blob);
        $this->em->flush();

        return $this->createJsonResponse(['blob_id' => $new_blob_id]);
    }

    //###########################################################################
    // get-current
    //###########################################################################

    public function getCurrentAction()
    {
        $ids = $this->in->getCleanValueArray('ids', 'uint', 'discard');

        if ($ids) {
            $blobs = $this->em->getRepository('DeskPRO:Blob')->getByIds($ids);
        } else {
            $blobs = [];
        }

        return $this->renderView('AgentBundle:MediaBrowser:current.html.twig', [
            'blobs' => $blobs,
        ]);
    }

    //###########################################################################
    // get-recent
    //###########################################################################

    public function getRecentAction($type = false)
    {
        if ($type) {
            $recent_blob_objects = $this->em->getRepository('DeskPRO:BlobObjectAttach')->getRecent(30, $type);
        } else {
            $recent_blob_objects = $this->em->getRepository('DeskPRO:BlobObjectAttach')->getRecent(30);
        }

        return $this->renderView('AgentBundle:MediaBrowser:recent.html.twig', [
            'type'                => $type,
            'recent_blob_objects' => $recent_blob_objects,
        ]);
    }

    //###########################################################################
    // update-blob
    //###########################################################################

    public function updateBlobAction($blob_id)
    {
        /** @var $blob \Application\DeskPRO\Entity\Blob */
        $blob = $this->em->find('DeskPRO:Blob', $blob_id);

        $blob['title']           = $this->in->getString('title');
        $blob['is_media_upload'] = true;
        $blob->getLabelManager()->setLabelsArray($this->in->getCleanValueArray('labels', 'string', 'discard'));

        $this->em->transactional(function ($em) use ($blob) {
            $em->persist($blob);
            $em->flush();
        });

        return $this->createJsonResponse(['success' => true]);
    }

    //###########################################################################
    // library
    //###########################################################################

    public function libraryAction($page = 1)
    {
        $types  = $this->in->getCleanValueArray('types', 'string', 'discard');
        $labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

        $qp = new \Application\DeskPRO\ORM\QueryPartial();
        $qp->setMaxResults(50)->setOrderBy('blob.id', 'DESC')->setFirstResult(($page - 1) * 50);

        $blob_objects = $this->em->getRepository('DeskPRO:BlobObjectAttach')->getLibraryResults($types, $labels, $qp);

        return $this->renderView('AgentBundle:MediaBrowser:library.html.twig', [
            'types'        => $types,
            'labels'       => $labels,
            'blob_objects' => $blob_objects,
        ]);
    }

    //###########################################################################
    // library-kb
    //###########################################################################

    public function libraryKbAction($category_id, $page = 1)
    {
        $labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

        /** @var $category \Application\DeskPRO\Entity\ArticleCategory */
        $category = $this->em->find('DeskPRO:ArticleCategory', $category_id);
        $cat_ids  = $category->getTreeIds(true);

        $qp = new \Application\DeskPRO\ORM\QueryPartial();
        $qp->setMaxResults(50)->setOrderBy('blob.id', 'DESC')->setFirstResult(($page - 1) * 50);

        $blob_objects = $this->em->getRepository('DeskPRO:BlobObjectAttach')->getKbLibraryResults($cat_ids, $labels, $qp);

        $category_hierarchy = $this->em->getRepository('DeskPRO:ArticleCateogory')->getFlatHierarchy();

        return $this->renderView('AgentBundle:MediaBrowser:library.html.twig', [
            'category_hierarchy' => $category_hierarchy,
            'labels'             => $labels,
            'blob_objects'       => $blob_objects,
        ]);
    }
}
