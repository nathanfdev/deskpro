<?php

namespace Application\DeskPRO\Usersource\Actions;

use Application\AgentBundle\Form\Type\NewOrganization;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
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

    protected function doHandle(DeskproContainer $container, Person $person, array $rawInput)
    {
        $value = $this->getValue($rawInput);
        /** @var Organization $orgRep */
        $orgRep = $container->getEm()->getRepository('DeskPRO:Organization');

        if (!$org = $orgRep->findOneByName($value)) {
            $neworg = new \Application\AgentBundle\Form\Model\NewOrganization();
            /** @var Form $form */
            $form = $container->get('form.factory')->create(NewOrganization::class, $neworg, [
                'csrf_protection'               => false,
                'csrf_double_submit_protection' => false,
            ]);
            $form->submit(['name' => $value], true);
            if ($form->isValid()) {
                $org = $neworg->save();
            }
        }

        if ($org) {
            $person->organization = $org;
        }
    }
}
