<?php

namespace Pintushi\Bundle\UserBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Pintushi\Bundle\UserBundle\DependencyInjection\Compiler\PrivilegeCategoryPass;

final class PintushiUserBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new PrivilegeCategoryPass());
    }
}
