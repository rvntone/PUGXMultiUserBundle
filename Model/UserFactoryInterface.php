<?php

namespace Rvntone\MultiUserBundle\Model;

interface UserFactoryInterface
{
    /**
     * @param string $class 
     */
    static function build($class);
}