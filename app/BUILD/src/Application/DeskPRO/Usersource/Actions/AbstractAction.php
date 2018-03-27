<?php

namespace Application\DeskPRO\Usersource\Actions;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParserCache\ArrayParserCache;

abstract class AbstractAction
{
    protected $filter;

    abstract public function getData();

    abstract public function setData($value);

    public function getFilter()
    {
        return (string) $this->filter;
    }

    public function setFilter($value)
    {
        $this->filter = (string) $value;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $class = get_class($this);

        return [
            'type'           => substr($class, strrpos($class, '\\') + 1),
            'data'           => $this->getData(),
            'filter_enabled' => strlen($this->getFilter()) ?: false,
            'filter'         => $this->getFilter(),
        ];
    }

    /**
     * @param array $action
     *
     * @return AbstractAction
     */
    public static function fromArray(array $action)
    {
        $class = '\Application\DeskPRO\Usersource\Actions\\'.@$action['type'];
        /** @var self $obj */
        $obj = new $class();
        $obj->setData(@$action['data']);
        $obj->setFilter(@$action['filter']);

        return $obj;
    }

    /**
     * @param $value
     * @param array $data
     *
     * @return string
     */
    protected function evaluate($value, array $data)
    {
        $lang = new ExpressionLanguage(new ArrayParserCache());
        try {
            return @$lang->evaluate($value, $data);
        } catch (\Exception $e) {
            //todo?
        }
    }

    /**
     * @param DeskproContainer $container
     * @param Person           $person
     * @param array            $rawInput
     *
     * @internal param array $data
     */
    public function handle(DeskproContainer $container, Person $person, array $rawInput)
    {
        if ($this->getFilter() && !$this->evaluate($this->getFilter(), ['user' => $rawInput])) {
            return;
        }

        if (!$this->getData() && !$this instanceof MakeAnAdmin) {
            return;
        }

        $this->doHandle($container, $person, $rawInput);
    }

    /**
     * @param DeskproContainer $container
     * @param Person           $person
     * @param array            $rawInput
     *
     * @return mixed
     *
     * @internal param array $data
     */
    abstract protected function doHandle(DeskproContainer $container, Person $person, array $rawInput);
}
