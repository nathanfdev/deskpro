<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Reports;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ReportDashboard;
use Application\DeskPRO\Entity\ReportDashboardPermission;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ReportDashboardType.
 */
class ReportDashboardType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ReportDashboard $report */
        $report = $builder->getData();
        $builder
            ->add('title', TextType::class, [
                'required' => !$report->isDefault(),
                'mapped'   => !$report->isDefault(),
            ])
            ->add('permissions', CollectionType::class, [
                'required'       => false,
                'error_bubbling' => false,
                'allow_add'      => true,
                'allow_delete'   => true,
                'entry_type'     => ReportDashboardPermissionType::class,
                'entry_options'  => [
                    'error_bubbling' => false,
                    'dashboard'      => $report,
                ],
            ])
            ->add('reports', ReportDashboardReportCollectionType::class, [
                'required'  => false,
                'dashboard' => $report,
                'person'    => $options['person'],
                'mapped'    => !$report->isDefault(),
            ])
            ->add('is_agent', ApiBooleanType::class, [
                'required'      => false,
                'property_path' => 'isAgent',
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ReportDashboard::class,
            ])
            ->setRequired('person')
            ->setAllowedTypes('person', Person::class)
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        /** @var Person $person */
        $person = $form->getConfig()->getOption('person');

        if ($data instanceof ReportDashboard) {
            $permissions = $data->getPermissions();
            foreach ($permissions as $permission) {
                if ($permission->getPerson()->isAdmin() || $permission->getPerson()->can_reports) {
                    $permissions->removeElement($permission);
                }
            }
            // handle new dashboards
            if (!$data->getId()) {
                // all created dashboards are custom ones
                $data->setIsDefault(false);
                $data->setPerson($person);
                // add access to yourself if not present for new dashboards
                $ownPermission = $data->getPermissions()->filter(function (ReportDashboardPermission $permission) use ($person) {
                    return $permission->getPerson() === $person;
                })->first();

                if (!$ownPermission) {
                    $ownPermission = new ReportDashboardPermission();
                    $ownPermission->setDashboard($data);
                    $ownPermission->setName(ReportDashboardPermission::FULL);
                    $ownPermission->setPerson($person);

                    $data->getPermissions()->add($ownPermission);
                }
            }
        }
    }
}
