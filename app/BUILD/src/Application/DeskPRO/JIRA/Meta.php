<?php

namespace Application\DeskPRO\JIRA;

class Meta
{
    /**
     * @var username authorized current oauth session
     */
    protected $api_username;

    /**
     * create meta-data.
     *
     * @var array
     */
    protected $projects = [];

    /**
     * schema for fields.
     *
     * @var array
     */
    protected $fields = [];

    /**
     * available statuses.
     *
     * @var array
     */
    protected $statuses = [];

    /**
     * available issue types.
     *
     * @var array
     */
    protected $issuetypes = [];

    protected $default_project;
    protected $default_issuetype;
    protected $default_fields_summary = [];
    protected $default_fields_list    = [];
    protected $system_fields          = ['project', 'issuetype', 'summary'];

    /**
     * @return array
     */
    public function toArray()
    {
        $ret = [];

        $ref = new \ReflectionObject($this);
        foreach ($ref->getProperties() as $prop) {
            $name       = $prop->getName();
            $ret[$name] = $this->{$name};
        }

        return $ret;
    }

    /**
     * @param array $data
     *
     * @return Meta
     */
    public static function fromArray(array $data)
    {
        $meta = new self();
        unset($data['system_fields']);
        foreach ($data as $k => $v) {
            if (property_exists($meta, $k)) {
                $meta->{$k} = $v;
            }
        }

        return $meta;
    }

    /**
     * @return array
     */
    public function getAllFields()
    {
        return array_values(array_unique(array_merge($this->default_fields_summary, $this->default_fields_list)));
    }

    public function getSystemFields()
    {
        return $this->system_fields;
    }

    /**
     * @return string
     */
    public function getApiUsername()
    {
        return $this->api_username;
    }

    public function setProjects(array $projects = [])
    {
        $this->projects = [];
        foreach ($projects as $project) {
            $this->projects[] = [
                'id'   => $project['id'],
                'key'  => $project['key'],
                'name' => $project['name'],
            ];
        }
    }

    public function setIssuetypes(array $issuetypes = [])
    {
        $this->issuetypes = [];
        foreach ($issuetypes as $issuetype) {
            $this->issuetypes[] = [
                'id'      => $issuetype['id'],
                'name'    => $issuetype['name'],
                'subtask' => false,
            ];
        }
    }

    public function setStatuses(array $statuses = [])
    {
        $this->statuses = [];
        foreach ($statuses as $status) {
            $this->statuses[] = [
                'id'   => $status['id'],
                'name' => $status['name'],
            ];
        }
    }

    public function setFields(array $fields = [])
    {
        $this->fields = [];
        foreach ($fields as $field) {
            $this->fields[] = [
                'id'     => $field['id'],
                'name'   => $field['name'],
                'custom' => $field['custom'],
                'schema' => $field['schema'],
            ];
        }
    }
}
