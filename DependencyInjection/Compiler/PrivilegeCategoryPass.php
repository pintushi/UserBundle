<?php

namespace Pintushi\Bundle\UserBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Pintushi\Bundle\UserBundle\Provider\Privilege\RolePrivilegeCategoryProvider;

class PrivilegeCategoryPass implements CompilerPassInterface
{
    const TAG = 'pintushi_user.privilege_category';
    const PRIORITY = 'priority';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(RolePrivilegeCategoryProvider::class)) {
            return;
        }
        $taggedServices = $container->findTaggedServiceIds(self::TAG);
        if (count($taggedServices) === 0) {
            return;
        }

        $registryDefinition = $container->getDefinition(RolePrivilegeCategoryProvider::class);
        foreach ($taggedServices as $serviceId => $tags) {
            $registryDefinition->addMethodCall('addProvider', [new Reference($serviceId)]);
        }
    }
}
