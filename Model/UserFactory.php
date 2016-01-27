<?php

namespace Rvntone\MultiUserBundle\Model;

use Rvntone\MultiUserBundle\Model\UserFactoryInterface;

/**
 * @author leonardo proietti (leonardo.proietti@gmail.com)
 */
class UserFactory implements UserFactoryInterface
{
    /**
     *
     * @param type $class
     * @return \Rvntone\MultiUserBundle\Model\class
     */
    public static function build($class)
    {        
        $user = new $class;        
        return $user;
    }
}