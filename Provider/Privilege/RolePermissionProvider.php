<?php

namespace Pintushi\Bundle\UserBundle\Provider\Privilege;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Translation\TranslatorInterface;

use Pintushi\Bundle\EntityConfigBundle\Config\ConfigManager;
use Pintushi\Bundle\SecurityBundle\Acl\AccessLevel;
use Pintushi\Bundle\SecurityBundle\Acl\Permission\PermissionManager;
use Pintushi\Bundle\SecurityBundle\Entity\Permission;
use Pintushi\Bundle\SecurityBundle\Form\Type\AclAccessLevelSelectorType;
use Pintushi\Bundle\SecurityBundle\Model\AclPermission;
use Pintushi\Bundle\SecurityBundle\Model\AclPrivilege;
use Pintushi\Bundle\UserBundle\Entity\AbstractRole;
use Pintushi\Bundle\UserBundle\Form\Handler\AclRoleHandler;
use Pintushi\Bundle\UserBundle\Provider\Privilege\RolePrivilegeAbstractProvider;
use Pintushi\Bundle\UserBundle\Provider\Privilege\RolePrivilegeCategoryProvider;

class RolePermissionProvider extends RolePrivilegeAbstractProvider
{
    /** @var PermissionManager */
    protected $permissionManager;

    /** @var ConfigManager */
    protected $configEntityManager;

    /** @var AbstractRole */
    protected $role;

    /**
     * @param TranslatorInterface           $translator
     * @param PermissionManager             $permissionManager
     * @param AclRoleHandler                $aclRoleHandler
     * @param RolePrivilegeCategoryProvider $categoryProvider
     * @param ConfigManager                 $configEntityManager
     */
    public function __construct(
        TranslatorInterface $translator,
        PermissionManager $permissionManager,
        AclRoleHandler $aclRoleHandler,
        RolePrivilegeCategoryProvider $categoryProvider,
        ConfigManager $configEntityManager
    ) {
        parent::__construct($translator, $categoryProvider, $aclRoleHandler);
        $this->permissionManager = $permissionManager;
        $this->configEntityManager = $configEntityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults($role)
    {
        $items = [];
        $allPrivileges = $this->preparePrivileges($role, 'entity');
        $categories = $this->categoryProvider->getPermissionCategories();

        foreach ($allPrivileges as $privilege) {
            /** @var AclPrivilege $privilege */
            $item = [
                'identity' => $privilege->getIdentity()->getId(),
                'label' => $privilege->getIdentity()->getName(),
                'group' => $this->getPrivilegeCategory($privilege, $categories),
                'permissions' => []
            ];
            $fields = $this->getFieldPrivileges($privilege->getFields());
            if (count($fields)) {
                $item['fields'] = $fields;
            }

            $items[] = $this->preparePermissions($privilege, $item);
        }

        return $items;
    }

    /**
     * @param ArrayCollection $fields
     *
     * @return array
     */
    protected function getFieldPrivileges(ArrayCollection $fields)
    {
        $result = [];
        foreach ($fields as $privilege) {
            /** @var AclPrivilege $privilege */
            $item =  [
                'identity' => $privilege->getIdentity()->getId(),
                'label' => $privilege->getIdentity()->getName(),
                'permissions' => []
            ];
            $result[] = $this->preparePermissions($privilege, $item);
        }

        return $result;
    }

    /**
     * @param AclPrivilege $privilege
     * @param array $item
     *
     * @return mixed
     */
    protected function preparePermissions(AclPrivilege $privilege, $item)
    {
        $permissions = [];
        foreach ($privilege->getPermissions() as $permissionName => $permission) {
            /** @var AclPermission $permission */
            $permissionEntity = $this->permissionManager->getPermissionByName($permission->getName());
            if ($permissionEntity && $this->isSupportedPermission($permissionName)) {
                $privilegePermission = $this->getPrivilegePermission(
                    $privilege,
                    $permissionEntity,
                    $permissionName,
                    $permission
                );
                $permissions[$permission->getName()] = $privilegePermission;
            }
        }
        $item['permissions'] = $this->sortPermissions($permissions);

        return $item;
    }

    /**
     * @param string $permissionName
     *
     * @return bool
     */
    protected function isSupportedPermission($permissionName)
    {
        return true;
    }

    /**
     * @param AclPrivilege $privilege
     * @param Permission $permissionEntity
     * @param string $permissionName
     * @param AclPermission $permission
     *
     * @return array
     */
    protected function getPrivilegePermission(
        AclPrivilege $privilege,
        Permission $permissionEntity,
        $permissionName,
        AclPermission $permission
    ) {
        $permissionLabel = $permissionEntity->getLabel() ? $permissionEntity->getLabel() : $permissionName;
        $permissionLabel = $this->translator->trans($permissionLabel);

        $permissionDescription = '';
        if ($permissionEntity->getDescription()) {
            $permissionDescription = $this->translator->trans($permissionEntity->getDescription());
        }

        $accessLevel = $permission->getAccessLevel();
        $accessLevelName = AccessLevel::getAccessLevelName($accessLevel);
        $valueText = $this->getRoleTranslationPrefix() . (empty($accessLevelName) ? 'NONE' : $accessLevelName);
        $valueText = $this->translator->trans($valueText);

        return [
            'id'                 => $permissionEntity->getId(),
            'name'               => $permissionEntity->getName(),
            'label'              => $permissionLabel,
            'description'        => $permissionDescription,
            'identity'           => $privilege->getIdentity()->getId(),
            'access_level'       => $accessLevel,
            'access_level_label' => $valueText
        ];
    }

    /**
     * @return string
     */
    protected function getRoleTranslationPrefix()
    {
        return 'pintushi.security.access_level.';
    }

    /**
     * Sort permissions. The CRUD permissions goes first and other ordered by the alphabet.
     *
     * @param array $permissions
     *
     * @return array
     */
    protected function sortPermissions(array $permissions)
    {
        $result = [];
        $permissionsList = ['VIEW', 'CREATE', 'EDIT', 'DELETE'];
        foreach ($permissionsList as $permissionName) {
            if (array_key_exists($permissionName, $permissions)) {
                $result[] = $permissions[$permissionName];
                unset($permissions[$permissionName]);
            }
        }

        if (count($permissions)) {
            ksort($permissions);
            foreach ($permissions as $permission) {
                $result[] = $permission;
            }
        }

        return $result;
    }
}
