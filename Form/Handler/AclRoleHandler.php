<?php

namespace Pintushi\Bundle\UserBundle\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Acl\Model\AclCacheInterface;
use Pintushi\Bundle\SecurityBundle\Acl\Group\AclGroupProviderInterface;
use Pintushi\Bundle\SecurityBundle\Acl\Permission\ConfigurablePermissionProvider;
use Pintushi\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Pintushi\Bundle\SecurityBundle\Acl\Persistence\AclPrivilegeRepository;
use Pintushi\Bundle\SecurityBundle\Filter\AclPrivilegeConfigurableFilter;
use Pintushi\Bundle\SecurityBundle\Model\AclPermission;
use Pintushi\Bundle\SecurityBundle\Model\AclPrivilege;
use Pintushi\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use Pintushi\Bundle\UserBundle\Entity\AbstractRole;

class AclRoleHandler
{
    /**
     * @var AclManager
     */
    protected $aclManager;

    /**
     * @var AclPrivilegeRepository
     */
    protected $privilegeRepository;

    /**
     * @var AclCacheInterface
     */
    protected $aclCache;

    /**
     * @var array
     */
    protected $privilegeConfig;

    /**
     * ['<extension_key>' => ['<allowed_group>', ...], ...]
     *
     * @var array
     */
    protected $extensionFilters = [];

    /** @var string */
    protected $configurableName = ConfigurablePermissionProvider::DEFAULT_CONFIGURABLE_NAME;

    /** @var AclPrivilegeConfigurableFilter */
    protected $configurableFilter;

    /**
     * @param FormFactory $formFactory
     * @param AclCacheInterface $aclCache
     * @param array $privilegeConfig
     */
    public function __construct(
        AclCacheInterface $aclCache,
        AclPrivilegeRepository $privilegeRepository,
        array $privilegeConfig
    )  {
        $this->aclCache = $aclCache;
        $this->privilegeRepository = $privilegeRepository;
        $this->privilegeConfig = $privilegeConfig;

        $this->loadPrivilegeConfigPermissions();
    }

    /**
     * @param AclManager $aclManager
     */
    public function setAclManager(AclManager $aclManager)
    {
        $this->aclManager = $aclManager;
    }

    /**
     * @param string $configurableName
     */
    public function setConfigurableName($configurableName)
    {
        $this->configurableName = $configurableName;
    }

    /**
     * @param AclPrivilegeConfigurableFilter $configurableFilter
     */
    public function setConfigurableFilter(AclPrivilegeConfigurableFilter $configurableFilter)
    {
        $this->configurableFilter = $configurableFilter;
    }

    /**
     * @param string $extensionKey
     * @param string $allowedGroup
     */
    public function addExtensionFilter($extensionKey, $allowedGroup)
    {
        if (!array_key_exists($extensionKey, $this->extensionFilters)) {
            $this->extensionFilters[$extensionKey] = [];
        }

        if (!in_array($allowedGroup, $this->extensionFilters[$extensionKey])) {
            $this->extensionFilters[$extensionKey][] = $allowedGroup;
        }
    }

    /**
     * Load privilege config permissions
     */
    protected function loadPrivilegeConfigPermissions()
    {
        foreach ($this->privilegeConfig as $configName => $config) {
            $this->privilegeConfig[$configName]['permissions']
                = $this->privilegeRepository->getPermissionNames($config['types']);
        }
    }

    /**
     * @param AbstractRole $role
     *
     * @return array
     *   key - privilege type (entity, action)
     *   value - ArrayCollection of AclPrivilege data
     */
    public function getAllPrivileges(AbstractRole $role)
    {
        $allPrivileges = [];
        $privileges = $this->getRolePrivileges($role);

        foreach ($this->privilegeConfig as $fieldName => $config) {
            $sortedPrivileges = $this->filterPrivileges($privileges, $config['types']);
            $this->applyOptions($sortedPrivileges, $config);
            $allPrivileges[$fieldName] = $sortedPrivileges;
        }

        return $allPrivileges;
    }

    /**
     * @param ArrayCollection|AclPrivilege[] $sortedPrivileges
     * @param array $config
     */
    protected function applyOptions(ArrayCollection $sortedPrivileges, array $config)
    {
        $hideDefault = !$config['show_default'];
        $fixValues = $config['fix_values'];

        if ($fixValues || $hideDefault) {
            foreach ($sortedPrivileges as $sortedPrivilege) {
                if ($hideDefault
                    && $sortedPrivilege->getIdentity()->getName() === AclPrivilegeRepository::ROOT_PRIVILEGE_NAME
                ) {
                    $sortedPrivileges->removeElement($sortedPrivilege);
                    continue;
                }

                if ($fixValues) {
                    foreach ($sortedPrivilege->getPermissions() as $permission) {
                        $permission->setAccessLevel((bool)$permission->getAccessLevel());
                    }
                }
            }
        }
    }

    /**
     * @param AbstractRole $role
     *
     * @return ArrayCollection|AclPrivilege[]
     */
    protected function getRolePrivileges(AbstractRole $role)
    {
        return $this->privilegeRepository->getPrivileges($this->aclManager->getSid($role), $this->getAclGroup());
    }

    /**
     * @param AbstractRole $role
     */
    public function processPrivileges(AbstractRole $role, array $privileges)
    {
        $formPrivileges = [];
        foreach ($this->privilegeConfig as $fieldName => $config) {
            if (array_key_exists($fieldName, $privileges)) {
                $privilegesArray = $privileges[$fieldName];
                $formPrivileges = array_merge($formPrivileges, $this->decodeAclPrivileges($privilegesArray, $config));
            }
        }

        array_walk(
            $formPrivileges,
            function (AclPrivilege $privilege) {
                $privilege->setGroup($this->getAclGroup());
            }
        );

        $this->privilegeRepository->savePrivileges(
            $this->aclManager->getSid($role),
            $this->configurableFilter->filter(new ArrayCollection($formPrivileges), $this->configurableName)
        );

        $this->aclCache->clearCache();
    }

    /**
     * @param ArrayCollection $privileges
     * @param array           $rootIds
     *
     * @return ArrayCollection|AclPrivilege[]
     */
    protected function filterPrivileges(ArrayCollection $privileges, array $rootIds)
    {
        $privileges = $this->configurableFilter->filter($privileges, $this->configurableName);

        return $privileges->filter(
            function (AclPrivilege $entry) use ($rootIds) {
                $extensionKey = $entry->getExtensionKey();

                // only current extension privileges
                if (!in_array($extensionKey, $rootIds, true)) {
                    return false;
                }

                // not filtered are allowed
                if (!array_key_exists($extensionKey, $this->extensionFilters)) {
                    return true;
                }

                // filter by groups
                return in_array($entry->getGroup(), $this->extensionFilters[$extensionKey], true);
            }
        );
    }

    /**
     * @param ArrayCollection|AclPrivilege[] $privileges
     * @param $value
     */
    protected function fxPrivilegeValue($privileges, $value)
    {
        foreach ($privileges as $privilege) {
            foreach ($privilege->getPermissions() as $permission) {
                $permission->setAccessLevel($permission->getAccessLevel() ? $value : 0);
            }
        }
    }

    /**
     * @return string
     */
    protected function getAclGroup()
    {
        return AclGroupProviderInterface::DEFAULT_SECURITY_GROUP;
    }

    /**
     * Encode array of AclPrivilege objects into array of plain privileges
     *
     * @param array $allPrivileges
     * @param bool $addExtensionName
     *
     * @return array
     */
    protected function encodeAclPrivileges($allPrivileges, $addExtensionName = true)
    {
        $formPrivileges = [];
        if (!$allPrivileges) {
            return $formPrivileges;
        }
        foreach ($allPrivileges as $key => $privilege) {
            /** @var AclPrivilege $privilege */
            $result = [
                'identity'    => [
                    'id'   => $privilege->getIdentity()->getId(),
                    'name' => $privilege->getIdentity()->getName(),
                ],
                'permissions' => [],
            ];
            $fields = $this->encodeAclPrivileges($privilege->getFields(), false);
            if ($fields) {
                $result['fields'] = $fields;
            }
            foreach ($privilege->getPermissions() as $permissionName => $permission) {
                /** @var AclPermission $permission */
                $result['permissions'][$permissionName] = [
                    'name'        => $permission->getName(),
                    'accessLevel' => $permission->getAccessLevel(),
                ];
            }
            $addExtensionName
                ? $formPrivileges[$privilege->getExtensionKey()][$key] = $result
                : $formPrivileges[$key] = $result;
        }

        return $formPrivileges;
    }

    /**
     * Decode array of plain privileges info into array of AclPrivilege objects
     *
     * @param array $privilegesArray
     * @param array $config
     *
     * @return array|AclPrivilege[]
     */
    protected function decodeAclPrivileges(array $privilegesArray, array $config)
    {
        $privileges = [];
        foreach ($privilegesArray as $privilege) {
            $aclPrivilege = new AclPrivilege();
            foreach ($privilege['permissions'] as $permission) {
                $aclPrivilege->addPermission(new AclPermission($permission['name'], $permission['accessLevel']));
            }
            $aclPrivilegeIdentity = new AclPrivilegeIdentity(
                $privilege['identity']['id'],
                $privilege['identity']['name']
            );
            $aclPrivilege->setIdentity($aclPrivilegeIdentity);
            if (isset($privilege['fields']) && count($privilege['fields'])) {
                $aclPrivilege->setFields(
                    new ArrayCollection($this->decodeAclPrivileges($privilege['fields'], $config))
                );
            }
            $privileges[] = $aclPrivilege;
        }
        if ($config['fix_values']) {
            $this->fxPrivilegeValue($privileges, $config['default_value']);
        }

        return $privileges;
    }
}
