<?php

namespace DeskPRO\Bundle\AppBundle\Helper;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Component\Util\EntityUtils;
use Doctrine\Common\Proxy\Proxy;

/**
 * This helper generates hashes for data. It can accept an arbitrary set of data, and return the same hash of that data every time it is run.
 *
 * Order of the input array is not relevenat.
 */
class ArbitraryHasher
{
    /**
     * Will always return a unique string hash of the $input, where $input can be a scalar, an object, or any \Traversable
     * or array of scalars/objects. The objects must be serializable, else a spl_object_hash is used.
     *
     * IMPORTANT: DomainObject instances are treated in a unique way, in that only the ID of the entity is used in the hash.
     *
     * This is meant to be used to hash $input's for a single request. The idea is to use this hash as a cache key that
     * is only in the memory of the current process. This hash should not be trusted for cache's that persist over more than
     * a single request.
     *
     * @param mixed $input
     *
     * @return string
     */
    public function generateHash($input)
    {
        $inputs = $this->collectInputs($input);

        $v = md5(json_encode($inputs));

        return $v;
    }

    protected function collectInputs($input)
    {
        if (is_scalar($input)) {
            return $input;
        }

        if ($input instanceof DomainObject || $input instanceof EntityInterface || $input instanceof Proxy || method_exists(
                $input,
                'getId'
            )
        ) {
            return EntityUtils::getIdentifier($input);
        }

        if ((is_array($input) || $input instanceof \ArrayAccess) && isset($input['id'])) {
            return $input['id'];
        }

        if (is_object($input) && isset($input->id)) {
            return $input->id;
        }

        if (is_array($input) || $input instanceof \Traversable) {
            $value = [];

            foreach ($input as $key => $val) {
                $value[$key] = $this->collectInputs($val);
            }

            if (isset($value['id'])) {
                return $value['id'];
            }

            return $value;
        }

        if (is_object($input)) {
            try {
                return serialize($input);
            } catch (\Exception $e) {
                return spl_object_hash($input);
            }
        }

        return $input;
    }
}
