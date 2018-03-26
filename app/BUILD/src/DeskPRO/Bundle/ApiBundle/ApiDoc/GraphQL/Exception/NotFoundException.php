<?php

namespace DeskPRO\Bundle\ApiBundle\ApiDoc\GraphQL\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class NotFoundException
 */
class NotFoundException extends NotFoundHttpException
{
    /**
     * @var ResolveInfo
     */
    protected $resolveInfo;

    /**
     * NotFoundException constructor.
     *
     * @param string      $message
     * @param ResolveInfo $info
     */
    public function __construct($message = null, ResolveInfo $info)
    {
        parent::__construct($message, null, 0);
        $this->resolveInfo;
    }

    /**
     * @return ResolveInfo
     */
    public function getResolveInfo()
    {
        return $this->resolveInfo;
    }
}
