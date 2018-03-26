<?php

namespace Application\DeskPRO\Service;

use Application\DeskPRO\CustomFields\CustomDataPersister;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\CustomFieldData;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\Form\Type\CustomFields\Definitions\ContextualChoiceDefinitionType;
use Application\DeskPRO\TicketLayout\Layout;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;

class CustomFieldManager
{
    const EVENT_FLUSH = 'flush';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $ff;

    /**
     * @var \Application\DeskPRO\EntityRepository\CustomFieldDefinition
     */
    protected $repDefinition;

    /**
     * @var \Application\DeskPRO\EntityRepository\CustomFieldData
     */
    protected $repData;

    /**
     * @var \Application\DeskPRO\CustomFields\CustomDataPersister
     */
    protected $persister;

    /**
     * @var array
     */
    protected $forms = [];

    public function __construct(EntityManager $em, FormFactory $ff)
    {
        $this->em            = $em;
        $this->ff            = $ff;
        $this->repDefinition = $em->getRepository('DeskPRO:CustomFieldDefinition');
        $this->repData       = $em->getRepository('DeskPRO:CustomFieldData');
        $this->persister     = new CustomDataPersister();
    }

    /**
     * @param DomainObject $owner
     * @param DomainObject $context
     * @param Layout       $layout
     *
     * @return ArrayCollection
     */
    public function getCustomDataForOwner(DomainObject $owner, DomainObject $context = null, Layout $layout = null)
    {
        $datas = [];

        if (!$owner['id']) {
            return new ArrayCollection();
        }

        // fetch fields values
        foreach ($this->repData->getAllDataForOwner($owner, $context, $layout) as $data) {
            /* @var $data CustomFieldData */
            $rootId = $data->root_definition['id'];

            if (!isset($datas[$rootId])) {
                $datas[$rootId] = [$data];
            } else {
                $datas[$rootId][] = $data;
            }
        }

        return new ArrayCollection($datas);
    }

    /**
     * @param FormInterface $form
     */
    public function flush(FormInterface $form = null)
    {
        if ($form && !$form->isSubmitted()) {
            return;
        }
        $this->persister->flush($this->em);
    }

    public function flush2(DomainObject $owner, DomainObject $context = null)
    {
        if (!$form = @$this->forms[$this->hash($owner, $context)]) {
            return;
        }

        $form->isValid() && $this->flush($form);
    }

    /**
     * creates form of defined custom fields.
     *
     * @param DomainObject $owner
     * @param DomainObject $context add contextual fields to form if context provided
     * @param Layout       $layout
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createFormForOwner(DomainObject $owner, DomainObject $context = null, Layout $layout = null, array $options = [])
    {
        $datas = $this->getCustomDataForOwner($owner, $context, $layout);

        // build types and bind values
        $builder = $this->ff->createNamedBuilder('custom_fields', 'form');

        foreach ($this->getDefinitions($owner, $context, $layout) as $def) {
            /* @var $def CustomFieldDefinition */
            $builder->add($this->createFieldFormBuilder($def, $owner, $context, $datas, $options));
        }

        $builder
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                // clean extra data
                if ($data = $event->getData()) {
                    $data = array_intersect_key($data, $event->getForm()->all());
                    $event->setData($data);
                }
            })
        ;

        return $this->forms[$this->hash($owner, $context)] = $builder->getForm();
    }

    /**
     * @param CustomFieldDefinition $definition
     * @param DomainObject          $owner
     * @param DomainObject          $context
     * @param ArrayCollection       $datas
     * @param array                 $options
     *
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    public function createFieldFormBuilder(CustomFieldDefinition $definition, DomainObject $owner, DomainObject $context = null, ArrayCollection $datas = null, $options = [])
    {
        if ($definition->parent) {
            throw new InvalidArgumentException('Can\'t create form for definition child');
        }

        if (null === $datas) {
            $datas = new ArrayCollection();
            if ($childData = $this->repData->getFieldData($definition, $owner, $context)) {
                $datas->set($definition['id'], $childData);
            }
        }

        $multiple = isset($definition['options']['multiple']) && $definition['options']['multiple'];
        $data     = $datas->get($definition['id']);
        if (!$multiple && is_array($data)) {
            $data = reset($data);
        }
        $options = array_merge($options, [
            'owner'     => $owner,
            'context'   => $context,
            'persister' => $this->persister,
        ]);

        return $this->ff->createNamedBuilder($definition['id'], $definition->createType(), $data, $options);
    }

    /**
     * @param CustomFieldDefinition $definition
     * @param DomainObject          $owner
     * @param DomainObject          $context
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createFieldForm(CustomFieldDefinition $definition, DomainObject $owner, DomainObject $context = null, $options = [])
    {
        if (!$definition['is_enabled']) {
            // todo exception?
            return;
        }

        return $this->createFieldFormBuilder($definition, $owner, $context, null, $options)->getForm();
    }

    /**
     * @param $fieldId
     * @param DomainObject $owner
     *
     * @throws \Symfony\Component\Form\Exception\InvalidArgumentException
     *
     * @return array|null
     */
    public function getFieldRawData($fieldId, DomainObject $owner, DomainObject $context = null)
    {
        if (!$owner['id']) {
            return;
        }

        if (!$definition = $this->repDefinition->find($fieldId)) {
            return;
        }

        if ($definition->parent) {
            throw new InvalidArgumentException('Can\'t create form for definition child');
        }

        if (!$data = $this->repData->getFieldRawData($definition, $owner)) {
            return;
        }

        $ret = [];
        foreach ($data as $row) {
            $ret[] = $row['value'] ? $row['title'] : $row['input'];
        }

        return count($ret) > 1 ? $ret : $ret[0];
    }

    /**
     * @param FormInterface $form1
     * @param FormInterface $form2
     *
     * @return FormInterface
     */
    public function merge(FormInterface $form1, FormInterface $form2)
    {
        foreach ($form2 as $name => $field) {
            /* @var $field FormInterface */
            $form2->remove($name);
            $form1->add($field);
        }

        if (false !== $k = array_search($form2, $this->forms)) {
            unset($this->forms[$k]);
        }

        return $form1;
    }

    /**
     * todo used for ContextualChoiceDefinition only (for now)
     * the only place this form used is Person view in Agent Interface (to define contextual choices for this person).
     *
     * @param DomainObject $context
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createDefinitionsFormForContext(DomainObject $context)
    {
        // root definitions
        $definitions = $this->repDefinition->findBy([
            'parent'        => null,
            'context_class' => ClassUtils::getClass($context),
            'is_enabled'    => true,
        ], ['display_order' => 'ASC']);

        $children = $this->buildDefinitionChildrenCollectionForContext($context);

        // build form
        $builder = $this->ff->createNamedBuilder('custom_fields_definitions', 'form');

        foreach ($definitions as $def) {
            /* @var $def CustomFieldDefinition */

            $builder->add('definition_'.$def['id'], new ContextualChoiceDefinitionType(), [
                'context'             => $context,
                'data'                => $def,
                'children_collection' => $children,
                'children_only'       => true,
                'label'               => $def['title'],
                'allow_edit'          => isset($def['options']['allow_edit']) ? $def['options']['allow_edit'] : false,
            ]);
        }

        return $builder->getForm();
    }

    /**
     * @param DomainObject $context
     *
     * @return ArrayCollection
     */
    protected function buildDefinitionChildrenCollectionForContext(DomainObject $context)
    {
        // def children for current context
        $collection = new ArrayCollection();

        if (!$context['id']) {
            return $collection;
        }

        $_children = $this->repDefinition->findBy([
            'context_class' => ClassUtils::getClass($context),
            'context_id'    => $context['id'],
        ], ['display_order' => 'ASC']);

        // build child tree
        foreach ($_children as $child) {
            if (!$child->parent) {
                continue;
            }
            $pid = $child->parent['id'];
            if (!$sub = $collection->get($pid)) {
                $sub = new ArrayCollection();
                $collection->set($pid, $sub);
            }
            $sub->add($child);
        }

        return $collection;
    }

    /**
     * @param DomainObject $owner
     * @param DomainObject $context
     * @param Layout       $layout
     *
     * @return array
     */
    public function getDefinitions(DomainObject $owner, DomainObject $context = null, Layout $layout = null)
    {
        return $this->repDefinition->getAllDefinitionsForOwner($owner, $context, $layout);
    }

    /**
     * @param $fieldId
     *
     * @return CustomFieldDefinition|null
     */
    public function getDefinition($fieldId)
    {
        return $this->repDefinition->findOneBy(['id' => $fieldId, 'is_enabled' => true]);
    }

    /**
     * @return FormFactory
     */
    public function getFormFactory()
    {
        return $this->ff;
    }

    protected function hash(DomainObject $owner, DomainObject $context = null)
    {
        return spl_object_hash($owner).($context ? spl_object_hash($context) : null);
    }
}
