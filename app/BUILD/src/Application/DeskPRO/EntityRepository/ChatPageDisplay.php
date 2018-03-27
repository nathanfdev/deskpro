<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ChatPageDisplay as ChatPageDisplayEntity;

class ChatPageDisplay extends AbstractEntityRepository
{
    public function getFromZone($zone, $department_context = null)
    {
        if ($department_context) {
            if (is_object($department_context)) {
                $department_context = $department_context['id'];
            }

            return $this->getEntityManager()->createQuery('
                SELECT d
                FROM DeskPRO:ChatPageDisplay d
                WHERE d.zone = :zone AND d.department = :department
            ')->setParameters(['zone' => $zone, 'department' => $department_context])->execute();
        } else {
            return $this->getEntityManager()->createQuery('
                SELECT d
                FROM DeskPRO:ChatPageDisplay d
                WHERE d.zone = :zone
            ')->setParameters(['zone' => $zone])->execute();
        }
    }

    public function getSection($department, $zone, $section)
    {
        try {
            $d = $this->findOneBy(['department' => $department ? $department['id'] : null, 'zone' => $zone, 'section' => $section]);

            return $d;
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * Just like getSectionData except it resolves to the best default match when a custom layout doesnt exist.
     *
     * @param $department
     * @param $zone
     * @param string $section
     */
    public function getSectionDataResolve($department, $zone, $section = 'default', &$is_resolved = null)
    {
        $page_data = App::getEntityRepository('DeskPRO:ChatPageDisplay')->getSectionData($department, $zone, $section);

        if ($page_data === null) {
            $is_resolved = true;
            if ($zone == 'create') {
                $page_data = App::getEntityRepository('DeskPRO:ChatPageDisplay')->getSectionData(null, $zone, $section);
            } else {
                // Default for view/modify is the create form from the same department,
                // or the default form from the default, or the default create if even that doesnt exist
                if ($department) {
                    $page_data = App::getEntityRepository('DeskPRO:ChatPageDisplay')->getSectionData($department, 'create', $section);
                }
            }
        }

        // If we get here with still nothing,
        // then we're auto-generating a 'create' form for everything there is in the system (oh, lawdy!)
        if ($page_data === null) {
            $is_resolved = true;
            $page_data   = $this->generateFullSectionData($zone);
        }

        return $page_data;
    }

    /**
     * Generates a page data array that has all enable-able components on it.
     *
     * @return array
     */
    public function generateFullSectionData($zone = 'create')
    {
        $page_data = [];

        if ($zone == 'create') {
            $page_data[] = [
                'id'         => 'person_name',
                'field_type' => 'person_name',
            ];
            $page_data[] = [
                'id'         => 'person_email',
                'field_type' => 'person_email',
            ];
        }

        $page_data[] = [
            'id'         => 'chat_department',
            'field_type' => 'chat_department',
        ];

        // Custom fields
        $fields = App::getSystemService('chat_fields_manager')->getFields();
        foreach ($fields as $f) {
            $page_data[] = [
                'id'         => 'chat_field['.$f->getId().']',
                'field_type' => 'chat_field',
                'field_id'   => $f->getId(),
            ];
        }

        return $page_data;
    }

    public function getSectionData($department, $zone, $section = 'default')
    {
        if ($department === null) {
            $data = App::getDb()->fetchColumn('
                SELECT data
                FROM chat_page_display
                WHERE department_id IS NULL AND zone = ? AND section = ?
            ', [$zone, $section]);
        } else {
            if (is_array($department) || is_object($department)) {
                $department = $department['id'];
            }
            $data = App::getDb()->fetchColumn('
                SELECT data
                FROM chat_page_display
                WHERE department_id = ? AND zone = ? AND section = ?
            ', [$department, $zone, $section]);
        }

        if (!$data) {
            return;
        }

        if ($data) {
            $data = unserialize($data);
        }

        if (!$data) {
            return [];
        }

        return $data;
    }

    public function getOrCreate($department, $zone, $section)
    {
        $d = null;
        try {
            $d = $this->findOneBy(['department' => $department ? $department['id'] : null, 'zone' => $zone, 'section' => $section]);
        } catch (\Exception $e) {
        }

        if (!$d) {
            $d             = new ChatPageDisplayEntity();
            $d->department = $department;
            $d['zone']     = $zone;
            $d['section']  = $section;
        }

        return $d;
    }
}
