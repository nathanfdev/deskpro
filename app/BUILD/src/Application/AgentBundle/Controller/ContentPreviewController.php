<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\Entity\Article;

class ContentPreviewController extends AbstractController
{
    public function articlePreviewAction($id)
    {
        $article = $this->getDoctrine()->getManager()->getRepository('DeskPRO:Article')->find($id);

        return $this->render('AgentBundle:ContentPreview:preview-article.html.twig', [
            'article' => $article,
        ]);
    }

    protected function requireRequestToken($action, $arguments = null)
    {
        return false;
    }
}
