<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Reports;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ReportDashboard;
use Application\DeskPRO\Entity\ReportDashboardPermission;
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
        $form   = $event->getForm();
        $data   = $event->getData();
        $person = $form->getConfig()->getOption('person');

        if ($data instanceof ReportDashboard && !$data->getId()) {
            // all created dashboards are custom ones
            $data->setIsDefault(false);

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
