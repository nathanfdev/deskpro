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

namespace Application\AgentBundle\Controller;

use Application\AgentBundle\Form\Model\NewTopic;
use Application\AgentBundle\Form\Type\NewTopic as NewTopicType;
use Application\AgentBundle\Validator\NewTopicValidator;
use Application\DeskPRO\ContentRevision\Util as ContentRevisionUtil;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Manual;
use Application\DeskPRO\Entity\PersonPref;
use Symfony\Component\HttpFoundation\Request;

class ManualController extends PublishController
{
    public function newTopicAction()
    {
        $brandId = $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand');

        $manuals = $this->em->getRepository(Manual::class)->findAll();

        $state = $this->em->getRepository(PersonPref::class)->getPrefForPersonId('agent.ui.state.new_topic', $this->person->id);

        $brands = $this->em->getRepository(Brand::class)->findAll();

        return $this->render('AgentBundle:Manual:new-topic.html.twig', [
            'manuals' => $manuals,
            'state'   => $state,
            'brands'  => $brands,
        ]);
    }

    public function newTopicSaveAction(Request $request)
    {
        $newTopic = new NewTopic($this->person);

        $formType = new NewTopicType();
        $form     = $this->get('form.factory')->create($formType, $newTopic);

        $this->db->executeUpdate("DELETE FROM people_prefs WHERE name = 'agent.ui.state.new_topic' AND person_id = ?", [$this->person->id]);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            $form->isValid();

            $validator = new NewTopicValidator();
            if (!$validator->isValid($newTopic)) {
                return $this->createJsonResponse([
                    'error'       => true,
                    'error_codes' => $validator->getErrorGroups(),
                ]);
            }
            $newTopic->save();

            $topic = $newTopic->getTopic();

            $rev = ContentRevisionUtil::findOrCreate($topic, ['title', 'content'], $this->person);
            if ($rev) {
                $this->em->persist($rev);
                $this->em->flush();
            }

            $this->em->getRepository(PersonPref::class)->deletePrefForPersonId('agent.ui.state.new_topic', $this->person->id);

            return $this->createJsonResponse([
                'success' => true,
                'news_id' => $topic['id'],
            ]);
        } else {
            return $this->createJsonResponse([
                'success' => false,
            ]);
        }
    }

    public function listAction($manual_id)
    {
        $manual = null;
        if ($manual_id) {
            /** @var Manual $manual */
            $manual = $this->em->getRepository(Manual::class)->find($manual_id);
        }

        if (!$manual) {
            throw $this->createNotFoundException();
        }

        $results = $manual->getTopics();

        $totalResults = count($results);

        $tpl = 'AgentBundle:Manual:filter.html.twig';

        $manualUserGroups = [];
        if ($manual) {
            $manualUserGroups = $this->db->fetchAllCol('
                SELECT usergroup_id
                FROM manual2usergroup
                WHERE manual_id = ?
            ', [$manual->getId()]);
        }

        return $this->render($tpl, [
            'results'        => $results,
            'manual'         => $manual,
            'cat_usergroups' => $manualUserGroups,
            'total_results'  => $totalResults,
            'num_pages'      => 1,
            'cur_page'       => 1,
            'showing_to'     => $totalResults,
        ]);
    }

    public function addCategoryFormAction($type = '')
    {
        $brandId = $this->in->getUInt('brand_id');

        /** @var Brand[] $brands */
        $brands = $this->em->getRepository(Brand::class)->findAll();

        return $this->render('AgentBundle:Publish:new-manual.html.twig', [
            'type'           => 'manual',
            'all_categories' => [],
            'brands'         => $brands,
            'brand_id'       => $brandId,
        ]);
    }
}
