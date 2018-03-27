<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\TextSnippet;
use Application\DeskPRO\Entity\TextSnippetCategory;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Orb\Util\Strings;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @ApiModes("all")
 */
class TextSnippetsController extends AbstractController
{
    //###################################################################################################################
    // filter-snippets
    //###################################################################################################################

    public function filterSnippetsAction($typename)
    {
        $category_id   = $this->in->getUint('category_id') ?: null;
        $filter_string = $this->in->getString('filter_string');
        $language_id   = $this->in->getUint('language_id');
        $draft_filter  = $this->in->getString('draft_filter') ?: 'off';

        $lang_repos = $this->container->getObjectLangRepository();

        $snippets = $this->em->getRepository('DeskPRO:TextSnippet')->getAllSnippetsForAgent(
            $typename,
            $this->person,
            1,
            500,
            $category_id
        );
        foreach ($this->container->getLanguageData()->getAll() as $lang) {
            $lang_repos->preloadObjectCollection($lang, $snippets);
        }

        if ($filter_string || $language_id) {
            $snippets_all = $snippets;
            $snippets     = [];

            $filter_string = Strings::utf8_strtolower($filter_string);

            foreach ($snippets_all as $snippet) {
                $match_lang   = false;
                $match_filter = false;
                $match_draft  = false;

                if ($language_id) {
                    foreach ($this->container->getLanguageData()->getAll() as $lang) {
                        if ($lang->getId() == $language_id) {
                            if ($snippet->getObjectTranslatable()->getObjectProp('title', $lang)) {
                                $match_lang = true;
                            }
                            break;
                        }
                    }
                } else {
                    $match_lang = true;
                }

                if ($filter_string) {
                    foreach ($this->container->getLanguageData()->getAll() as $lang) {
                        $test = $snippet->getObjectTranslatable()->getObjectProp('title', $lang);
                        $test = Strings::utf8_strtolower($test);
                        if (strpos($test, $filter_string) !== false) {
                            $match_filter = true;
                            break;
                        }
                    }

                    if (!$match_filter) {
                        foreach ($this->container->getLanguageData()->getAll() as $lang) {
                            $test = $snippet->getObjectTranslatable()->getObjectProp('snippet', $lang);
                            $test = Strings::utf8_strtolower($test);
                            if (strpos($test, $filter_string) !== false) {
                                $match_filter = true;
                                break;
                            }
                        }
                    }
                } else {
                    $match_filter = true;
                }

                switch ($draft_filter) {
                    case 'off':
                        $match_draft = $snippet->is_draft === false;
                        break;
                    case 'on':
                        $match_draft = true;
                        break;
                    case 'only':
                        $match_draft = $snippet->is_draft === false;
                        break;
                }

                if ($match_lang && $match_filter && $match_draft) {
                    $snippets[] = $snippet;
                }
            }
        }

        $data = ['snippets' => []];
        foreach ($snippets as $snippet) {
            $data['snippets'][] = $snippet->toApiData();
        }

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // get-snippet
    //###################################################################################################################

    public function getSnippetAction($typename, $id)
    {
        $snippet = $this->em->find('DeskPRO:TextSnippet', $id);
        if (!$snippet) {
            throw $this->createNotFoundException();
        }

        foreach ($this->container->getLanguageData()->getAll() as $lang) {
            $this->container->getObjectLangRepository()->preloadObject($lang, $snippet);
        }

        $data = ['snippet' => $snippet->toApiData()];

        return $this->createApiResponse($data);
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

        $this->em->persist($snippet);
        $this->em->flush();

        if ($this->in->checkIsset('is_draft')) {
            $snippet->is_draft = $this->in->getBool('is_draft');
        }

        foreach ($this->container->getLanguageData()->getAll() as $lang) {
            $this->container->getObjectLangRepository()->preloadObject($lang, $snippet);
        }

        foreach ($this->container->getLanguageData()->getAll() as $lang) {
            $lang_id = $lang->getId();

            $title       = $this->in->getString("title.$lang_id");
            $snippet_val = $this->in->getString("snippet.$lang_id");

            $rec = $this->container->getObjectLangRepository()->setRec($lang, $snippet, 'title', $title);
            $this->em->persist($rec);

            $rec = $this->container->getObjectLangRepository()->setRec($lang, $snippet, 'snippet', $snippet_val);
            $this->em->persist($rec);
        }

        $this->em->flush();

        return $this->createApiCreateResponse(
            ['snippet_id' => $snippet->id],
            $this->generateUrl(
                'api_textsnippets_get',
                ['id' => $snippet->id, 'typename' => $typename],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
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

        return $this->createApiResponse(['success' => true, 'snippet_id' => $id]);
    }

    //###################################################################################################################
    // list-categories
    //###################################################################################################################

    public function listCategoriesAction($typename)
    {
        $snippet_cats = $this->em->getRepository('DeskPRO:TextSnippetCategory')->getCatsForAgent(
            $typename,
            $this->person
        );

        foreach ($this->container->getLanguageData()->getAll() as $lang) {
            $this->container->getObjectLangRepository()->preloadObjectCollection($lang, $snippet_cats);
        }

        $data = [
            'snippet_cats' => [],
        ];

        foreach ($snippet_cats as $cat) {
            $data['snippet_cats'][] = $cat->toApiData();
        }

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // get-category
    //###################################################################################################################

    public function getCategoryAction($typename, $id)
    {
        $cat = $this->em->find('DeskPRO:TextSnippetCategory', $id);

        if (!$cat || $cat->typename != $typename) {
            throw $this->createNotFoundException();
        }

        $data = ['snippet_cat' => $cat->toApiData()];

        return $this->createApiResponse($data);
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

        return $this->createApiCreateResponse(
            ['category_id' => $cat->id],
            $this->generateUrl(
                'api_textsnippets_cats_get',
                ['id' => $cat->id, 'typename' => $typename],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
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
            WHERE category_id = ?
        ',
            [$cat->getId()]
        );

        if ($has_snippets) {
            return $this->createApiErrorResponse(
                409,
                'The category is not empty. Delete existing snippets and try again.',
                409
            );
        }

        $this->em->remove($cat);
        $this->em->flush();

        return $this->createApiResponse(
            [
                'success'     => true,
                'category_id' => $id,
            ]
        );
    }
}
