<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\DeskPRO\Usersource\Actions;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\EntityRepository\Organization;
use Symfony\Component\Form\Form;

class AddToOrg extends AbstractAction
{
    protected $title;

    public function getData()
    {
        return $this->title;
    }

    public function setData($value)
    {
        $this->title = (string) $value;
    }

    protected function getValue(array $data)
    {
        return $this->getData();
    }

    protected function doHandle(DeskproContainer $container, array $data)
    {
        $value = $this->getValue($data);
        /** @var Organization $orgRep */
        $orgRep = $container->getEm()->getRepository('DeskPRO:Organization');

        if (!$org = $orgRep->findOneByName($value)) {
            $neworg   = new \Application\AgentBundle\Form\Model\NewOrganization();
            $formType = new \Application\AgentBundle\Form\Type\NewOrganization();
            /** @var Form $form */
            $form = $container->get('form.factory')->create(
                $formType,
                $neworg,
                // this was REALLY unexpected and hard to find!
                ['csrf_double_submit_protection' => false]
            );
            $form->submit(['name' => $value], true);
            if ($form->isValid()) {
                $org = $neworg->save();
            }
        }

        if ($org) {
            $this->getPerson($data)->organization = $org;
        }
    }
}
