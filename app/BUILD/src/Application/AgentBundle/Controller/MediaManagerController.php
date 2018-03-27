<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller;

class MediaManagerController extends AbstractController
{
    //###########################################################################
    // window
    //###########################################################################

    public function windowAction()
    {
        return $this->render('AgentBundle:MediaManager:media-window.html.twig', [

        ]);
    }

    //###########################################################################
    // upload
    //###########################################################################

    public function uploadAction()
    {
        return $this->render('AgentBundle:MediaManager:upload.html.twig', [

        ]);
    }

    //###########################################################################
    // browse
    //###########################################################################

    public function browseAction()
    {
        $blobs = $this->container->getEm()->createQuery('
            SELECT b
            FROM DeskPRO:Blob b
            WHERE b.is_media_upload = true
            ORDER BY b.id DESC
        ')->setMaxResults(150)->execute();

        return $this->render('AgentBundle:MediaManager:browse.html.twig', [
            'blobs' => $blobs,
        ]);
    }
}
