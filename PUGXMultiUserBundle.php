<?php

namespace Rvntone\MultiUserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Rvntone\MultiUserBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PUGXMultiUserBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OverrideServiceCompilerPass());
    }
}
