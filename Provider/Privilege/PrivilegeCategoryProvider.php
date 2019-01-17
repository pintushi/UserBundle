<?php

namespace Pintushi\Bundle\UserBundle\Provider\Privilege;

use Pintushi\Bundle\UserBundle\Model\PrivilegeCategory;
use Pintushi\Bundle\SecurityBundle\Configuration\PrivilegeCategoryConfiguration;
use Oro\Component\Config\Loader\CumulativeConfigLoader;
use Oro\Component\Config\Loader\YamlCumulativeFileLoader;

class PrivilegeCategoryProvider implements PrivilegeCategoryProviderInterface
{
    const NAME = 'platform';

    protected $configPath = 'Resources/config/app/privilege_category.yml';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getRolePrivilegeCategory()
    {
        $configs = $this->loadConfiguration();

        $categories = [];

        foreach($configs as $id => $config) {
            $categories[] = new PrivilegeCategory($id, $config['label'], $config['tab'], $config['priority'], $config['visible']);
        }

        return $categories;
    }

    protected function loadConfiguration()
    {
        $configLoader = new CumulativeConfigLoader('pintushi_privilege_category', new YamlCumulativeFileLoader($this->configPath));

        $resources = $configLoader->load();

        $config = [];
        foreach ($resources as $resource) {
            if (array_key_exists(PrivilegeCategoryConfiguration::ROOT_NODE_NAME, $resource->data)) {
                $config[] = $resource->data[PrivilegeCategoryConfiguration::ROOT_NODE_NAME];
            }
        }

        $privilegeCategoryConfiguration = new PrivilegeCategoryConfiguration();

        return $privilegeCategoryConfiguration->processConfiguration($config);
    }
}
