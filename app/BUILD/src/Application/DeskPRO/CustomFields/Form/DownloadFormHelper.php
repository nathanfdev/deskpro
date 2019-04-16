<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\CustomFields\Form;

use Application\DeskPRO\Entity\CustomDefAbstract;

class DownloadFormHelper extends FormHelper
{
    /**
     * @param CustomDefAbstract $field
     * @param array             $formData
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function buildForm(CustomDefAbstract $field, array $formData)
    {
        $form = parent::buildForm($field, $formData);

        if (isset($formData['sys_name']) && $formData['sys_name'] === 'eula') {
            /** @var Application\DeskPRO\CustomFields\Form\Model\ChoiceField $editField */
            $editField                       = $form->getData();
            $editField->allowedChoiceOptions = ['eula', 'eula_format'];
        }

        return $form;
    }
}
