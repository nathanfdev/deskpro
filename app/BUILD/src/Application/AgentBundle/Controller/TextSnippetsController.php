<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\Entity\TextSnippet;
use Application\DeskPRO\Entity\TextSnippetCategory;
use Orb\Util\Strings;

class TextSnippetsController extends AbstractController
{
    public function requireRequestToken($action, $arguments = null)
    {
        return false;
    }

    //###################################################################################################################
    // get-widget-shell
    //###################################################################################################################

    public function getWidgetShellAction($typename)
    {
        $snippet_cats = $this->em->getRepository('DeskPRO:TextSnippetCategory')->getCatsForAgent(
            $typename,
            $this->person
        );

        if ($typename != 'tickets' && $typename != 'chat') {
            throw $this->createNotFoundException();
        }

        return $this->render(
            "AgentBundle:TextSnippets:$typename-widget-shell.html.twig",
            [
                'snippet_cats' => $snippet_cats,
            ]
        );
    }

    //###################################################################################################################
    // reload-client
    //###################################################################################################################

    public function reloadClientAction($typename)
    {
        $snippet_cats = $this->em->getRepository('DeskPRO:TextSnippetCategory')->getCatsForAgent(
            $typename,
            $this->person
        );

        foreach ($this->container->getLanguageData()->getAll() as $lang) {
            $this->container->getObjectLangRepository()->preloadObjectCollection($lang, $snippet_cats);
        }

        $snippets_count = $this->em->getRepository('DeskPRO:TextSnippet')->countSnippetsForAgent(
            $typename,
            $this->person
        );
        $per_page  = 250;
        $num_pages = ceil($snippets_count / $per_page);

        $data = [
            'typename'       => $typename,
            'snippets_count' => $snippets_count,
            'num_pages'      => $num_pages,
            'snippet_cats'   => [],
        ];

        foreach ($snippet_cats as $cat) {
            $data['snippet_cats'][] = $cat->toApiData();
        }

        return $this->createJsonResponse($data);
    }

    //###################################################################################################################
    // reload-client-batch
    //###################################################################################################################

    public function reloadClientBatchAction($typename, $batch = 1)
    {
        $snippets = $this->em->getRepository('DeskPRO:TextSnippet')->getAllSnippetsForAgent(
            $typename,
            $this->person,
            $batch,
            250
        );
        foreach ($this->container->getLanguageData()->getAll() as $lang) {
            $this->container->getObjectLangRepository()->preloadObjectCollection($lang, $snippets);
        }

        $data = ['snippets' => []];
        foreach ($snippets as $snippet) {
            $data['snippets'][] = $snippet->toApiData();
        }

        return $this->createJsonResponse($data);
    }

    //###################################################################################################################
    // filter-snippets
    //###################################################################################################################

    public function filterSnippetsAction($typename)
    {
        $category_id   = $this->in->getUint('category_id') ?: null;
        $filter_string = $this->in->getString('filter_string') ?: null;
        $language_id   = $this->in->getUint('language_id') ?: null;

        /** @var \Application\DeskPRO\EntityRepository\TextSnippet $rep */
        $rep = $this->em->getRepository('DeskPRO:TextSnippet');

        $results = $rep->filterSnippetsForAgent($filter_string, $typename, $this->person, 1, 2500, $category_id, $language_id);

        return $this->createJsonResponse(['snippets' => $results]);
    }

    //###################################################################################################################
    // get-snippet
    //###################################################################################################################

    public function getSnippetAction($typename, $id)
    {
        $snippet = $this->em->find(TextSnippet::class, $id);
        if (!$snippet) {
            throw $this->createNotFoundException();
        }

        foreach ($this->container->getLanguageData()->getAll() as $lang) {
            $this->container->getObjectLangRepository()->preloadObject($lang, $snippet);
        }

        $data = ['snippet' => $snippet->toApiData()];

        return $this->createJsonResponse($data);
    }

    //###################################################################################################################
    // save-snippet
    //###################################################################################################################

    public function saveSnippetAction($typename, $id)
    {
        if ($id) {
            $snippet = $this->em->find('DeskPRO:TextSnippet', $id);
            if (!$snippet) {
                throw $this->createNotFoundException();
            }
        } else {
            $snippet = new TextSnippet();
        }

        $category = $this->em->find('DeskPRO:TextSnippetCategory', $this->in->getUInt('category_id'));
        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }
        $snippet->category = $category;

        $snippet->setShortcutCode($this->in->getString('shortcut_code'));
        $snippet->is_draft = $this->in->getBool('is_draft');

        $this->em->persist($snippet);
        $this->em->flush();

        foreach ($this->container->getLanguageData()->getAll() as $lang) {
            $this->container->getObjectLangRepository()->preloadObject($lang, $snippet);
        }

        foreach ($this->container->getLanguageData()->getAll() as $lang) {
            $lang_id = $lang->getId();

            $title       = $this->in->getString("title.$lang_id");
            $snippet_val = $this->in->getHtml("snippet.$lang_id");
            $snippet_val = Strings::prepareWysiwygHtml($snippet_val);

            if ($title || $snippet_val) {
                $rec = $this->container->getObjectLangRepository()->setRec($lang, $snippet, 'title', $title);
                $this->em->persist($rec);

                $rec = $this->container->getObjectLangRepository()->setRec($lang, $snippet, 'snippet', $snippet_val);
                $this->em->persist($rec);
            }
        }

        $this->em->flush();

        return $this->createJsonResponse(['success' => true, 'snippet' => $snippet->toApiData()]);
    }

    //###################################################################################################################
    // delete-snippet
    //###################################################################################################################

    public function deleteSnippetAction($typename, $id)
    {
        $snippet = $this->em->find('DeskPRO:TextSnippet', $id);
        if (!$snippet) {
            throw $this->createNotFoundException();
        }

        $this->em->remove($snippet);
        $this->em->flush();

        return $this->createJsonResponse(['success' => true, 'snippet_id' => $id]);
    }

    //###################################################################################################################
    // save-category
    //###################################################################################################################

    public function saveCategoryAction($typename, $id)
    {
        if ($id) {
            $cat = $this->em->find('DeskPRO:TextSnippetCategory', $id);
            if (!$cat) {
                throw $this->createNotFoundException();
            }
        } else {
            $cat           = new TextSnippetCategory();
            $cat->typename = $typename;
            $cat->person   = $this->person;
        }

        $cat->is_global = ($this->in->getString('perm_type') == 'global');

        $this->em->persist($cat);
        $this->em->flush();

        $global_title = $this->in->getString('title');

        foreach ($this->container->getLanguageData()->getAll() as $lang) {
            $lang_id = $lang->getId();

            $title = $this->in->getString("title.$lang_id");
            if (!$title) {
                $title = $global_title;
            }

            $rec = $this->container->getObjectLangRepository()->setRec($lang, $cat, 'title', $title);
            $this->em->persist($rec);
        }

        $this->em->flush();

        return $this->createJsonResponse(['success' => true, 'category' => $cat->toApiData()]);
    }

    //###################################################################################################################
    // delete-category
    //###################################################################################################################

    public function deleteCategoryAction($typename, $id)
    {
        $cat = $this->em->find('DeskPRO:TextSnippetCategory', $id);
        if (!$cat) {
            throw $this->createNotFoundException();
        }

        $has_snippets = $this->db->fetchColumn(
            '
            SELECT COUNT(*)
            FROM text_snippets
            WHERE category_id = ? AND is_draft = 0
        ',
            [$cat->getId()]
        );

        $has_draft_snippets = $this->db->fetchColumn(
            '
            SELECT COUNT(*)
            FROM text_snippets
            WHERE category_id = ? AND is_draft = 1
        ',
            [$cat->getId()]
        );

        if ($has_snippets || $has_draft_snippets) {
            return $this->createJsonResponse(
                [
                    'error'        => true,
                    'error_code'   => 'not_empty',
                    'count'        => $has_snippets,
                    'count_drafts' => $has_draft_snippets,
                ]
            );
        }

        $this->em->remove($cat);
        $this->em->flush();

        return $this->createJsonResponse(
            [
                'success'     => true,
                'category_id' => $id,
            ]
        );
    }
}
