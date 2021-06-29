<?php

namespace Application\AgentBundle\Validator;

use Application\AgentBundle\Form\Model\NewOrganization;
use Application\DeskPRO\App;
use Application\DeskPRO\CustomFields\Handler\HandlerAbstract;
use Application\DeskPRO\Entity\Organization;
use Orb\Validator\AbstractValidator;

/**
 * Class NewOrganizationValidator.
 */
class NewOrganizationValidator extends AbstractValidator
{
    /**
     * {@inheritdoc}
     *
     * @param NewOrganization $value
     *
     * @throws \Exception
     */
    protected function checkIsValid($value)
    {
        if ($value->name) {
            $organizations = App::$container->get('doctrine.orm.default_entity_manager')->getRepository(Organization::class)
                ->createQueryBuilder('a')
                ->where('upper(a.name) = upper(:name)')
                ->setParameter('name', $value->name)
                ->getQuery()
                ->execute();

            if (!empty($organizations)) {
                $this->addError('title.missing', ['message' => 'Organization already exists', 'field' => 'name']);
            }
        }

        $fields = App::$container->get('custom_field_manager')->getAvailableOrganizationDefs();

        foreach ($fields as $field) {
            $errors = $field->getHandler()->validateFormData($value->custom_fields ?: [], HandlerAbstract::CONTEXT_AGENT);
            foreach ($errors as $code) {
                $title = $field->getTitle();
                $str   = "Please correct $title";
                $code  = str_replace('field_'.$field->getId().'.', '', $code);
                switch ($code) {
                    case 'required':
                        $str = "$title is required";

                        break;
                    case 'min_length':
                        $str = "$title is too short";

                        break;
                    case 'max_length':
                        $str = "$title is too long";

                        break;
                    case 'regex':
                        $str = "$title is invalid";

                        break;
                }

                $this->addError('org.'.$field->getId().'.'.$code, ['message' => $str, 'field' => 'org_field_'.$field->getId()]);
            }
        }

        if ($this->errors) {
            return false;
        }

        return true;
    }
}
