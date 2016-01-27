<?php

namespace Rvntone\MultiUserBundle\Tests\Stub;

use Rvntone\MultiUserBundle\Model\UserFactoryInterface;

class CustomUserFactory implements UserFactoryInterface
{    
    public static function build($class) {
        return new AnotherUser;
    }
}