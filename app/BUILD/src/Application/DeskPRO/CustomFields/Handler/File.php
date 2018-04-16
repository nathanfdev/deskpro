<?php

namespace Application\DeskPRO\CustomFields\Handler;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Blob;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class File.
 */
class File extends HandlerAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getWidgetName()
    {
        return 'collection';
    }

    /**
     * @return array
     */
    public function getWidgetOptions()
    {
        return [
            'entry_type'   => HiddenType::class,
            'allow_add'    => true,
            'allow_delete' => true,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function renderFormHtml($formView, array $templateVars = [])
    {
        foreach ($formView->children as $child) {
            $value = $child->vars['value'];
            if ($value instanceof Blob) {
                $child->vars['value'] = $value->getId();
            }
        }

        $formView->vars['attr']['data-multiple'] = $this->field_def->getOption('multiple');

        return parent::renderFormHtml($formView, $templateVars);
    }

    /**
     * {@inheritdoc}
     */
    public function validateFormData(array $formData, $context = self::CONTEXT_USER, $contextData = null)
    {
        $data = $this->findValue($formData);
        if (!is_array($data)) {
            $data = [];
        }

        // convert to custom data for the file validator
        $customDataCollection = new ArrayCollection();
        foreach ($data as $blob) {
            $customData = $this->field_def->createCustomData();
            $customData->setField($this->field_def);
            $customData->setRootField($this->field_def);
            $customData->setValue($blob instanceof Blob ? $blob->getId() : $blob);

            $customDataCollection->add($customData);
        }

        $errors = App::$container->get('validator')->validate($customDataCollection, [
            new AppAssert\CustomField\File([
                'custom_def' => $this->field_def,
                'context'    => 'agent',
            ]),
        ]);

        if ($errors->count() > 0) {
            $errorCodes = [];
            foreach ($errors as $error) {
                $errorCodes[] = $error->getCode();
            }

            return $this->makeErrorArray($errorCodes);
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getDataFromForm(array $formData)
    {
        $value = $this->findValue($formData);

        return [
            [$this->field_def['id'], 'value', $value],
        ];
    }

    /**
     * @param array $formData
     * @param null  $default
     *
     * @return mixed|null
     */
    private function findValue(array $formData, $default = null)
    {
        $names = $this->getAllFormFieldNames();
        foreach ($names as $name) {
            if (!empty($formData[$name]) || (isset($formData[$name]) && (string) $formData[$name] === '0')) {
                $value = $formData[$name];
                if (is_array($value)) {
                    $value = array_map(function ($item) {
                        return $item instanceof Blob ? $item->getId() : (int) $item;
                    }, $value);
                }

                return $value;
            }
        }

        return $default;
    }
}
