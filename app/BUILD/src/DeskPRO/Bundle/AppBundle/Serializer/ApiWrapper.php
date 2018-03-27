<?php

namespace DeskPRO\Bundle\AppBundle\Serializer;

use Doctrine\MongoDB\CursorInterface;
use JMS\Serializer\Annotation as JMS;
use Pagerfanta\Pagerfanta;

/**
 * Class ApiWrapper.
 *
 * todo refactor api wrapper model to be able to set implicit way.
 * todo rename as it's used not only for api.
 */
class ApiWrapper
{
    /**
     * @JMS\Groups("wrapper")
     *
     * @var array|Pagerfanta
     */
    protected $data = [];

    /**
     * @JMS\Groups("wrapper")
     * @JMS\Type("map")
     *
     * @var array
     */
    protected $meta;

    /**
     * @JMS\Exclude()
     *
     * It will be filled up just after serialization ends
     *
     * @var array
     */
    protected $linked = [];

    /**
     * Constructor.
     *
     * @param mixed $data
     * @param array $meta
     */
    public function __construct($data, array $meta = [])
    {
        $this->meta = new \ArrayObject($meta);
        if (!$this->checkPagination($data)) {
            $this->data = $data;
        }
    }

    /**
     * @param mixed $data
     *
     * @return bool
     */
    protected function checkPagination($data)
    {
        if ($data instanceof Pagerfanta) {
            $pagination = [
                'total'        => $data->count(),
                'count'        => count($data->getCurrentPageResults()),
                'per_page'     => $data->getMaxPerPage(),
                'current_page' => $data->getCurrentPage(),
                'total_pages'  => $data->getNbPages(),
            ];

            $results = $data->getCurrentPageResults();
            if ($results instanceof \ArrayIterator) {
                $results = $results->getArrayCopy();
            } elseif ($results instanceof CursorInterface) {
                $results = array_values($results->toArray());
            }

            $this->data               = $results;
            $this->meta['pagination'] = $pagination;

            return true;
        } elseif ($data instanceof OffsetList) {
            $pagination = [
                'total'    => $data->getTotal(),
                'per_page' => $data->getCount(),
                'offset'   => $data->getOffset(),
            ];

            $this->data               = $data->getData();
            $this->meta['pagination'] = $pagination;

            return true;
        }

        return false;
    }

    /**
     * @return array|Pagerfanta
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array|Pagerfanta $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}
