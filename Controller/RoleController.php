<?php

namespace Pintushi\Bundle\UserBundle\Controller;

use Pintushi\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pintushi\Bundle\UserBundle\Entity\Role;
use Pintushi\Bundle\UserBundle\Form\Handler\AclRoleHandler;
use Pintushi\Bundle\UserBundle\Model\PrivilegeCategory;
use Pintushi\Bundle\UserBundle\Provider\Privilege\RolePrivilegeCapabilityProvider;
use Pintushi\Bundle\UserBundle\Provider\Privilege\RolePrivilegeCategoryProvider;
use Pintushi\Bundle\UserBundle\Repository\RoleRepository;
use Pintushi\Bundle\UserBundle\Provider\Privilege\RolePermissionProvider;
use Pintushi\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RoleController extends AbstractController
{
    private $rolePrivilegeCapabilityProvider;

    private $rolePrivilegeCategoryProvider;

    private $aclRoleHandler;

    private $roleRepository;

    private $userPrivileges;

    private $rolePermissionProvider;

    private $entityManager;

    public function __construct(
        RolePrivilegeCapabilityProvider $rolePrivilegeCapabilityProvider,
        RolePrivilegeCategoryProvider $rolePrivilegeCategoryProvider,
        AclRoleHandler $aclRoleHandler,
        RoleRepository $roleRepository,
        RolePermissionProvider $rolePermissionProvider,
        ObjectManager $entityManager,
        $userPrivileges
    ) {
        $this->rolePrivilegeCapabilityProvider = $rolePrivilegeCapabilityProvider;
        $this->rolePrivilegeCategoryProvider = $rolePrivilegeCategoryProvider;
        $this->aclRoleHandler = $aclRoleHandler;
        $this->roleRepository = $roleRepository;
        $this->rolePermissionProvider = $rolePermissionProvider;
        $this->entityManager = $entityManager;
        $this->userPrivileges = $userPrivileges;
    }

    public function view(Role $data)
    {
        return [
            'entity' => $data,
            'role_permissions' => $this->rolePermissionProvider->getResults($data),
            'tabs_options' => [
                'data' => $this->getTabListOptions()
            ],
            'capability_set_options' => [
                'data' => $this->rolePrivilegeCapabilityProvider->getCapabilities($data),
                'tab_ids' => $this->rolePrivilegeCategoryProvider->getTabList(),
                'readonly' => true
            ],
            // TODO: it is a temporary solution. In a future it is planned to give an user a choose what to do:
            // completely delete a role and un-assign it from all users or reassign users to another role before
            'allow_delete' => $data->getId() && !$this->roleRepository->hasAssignedUsers($data)
        ];
    }

    /**
     * @Route("roles/available_permissions",
     *  name="api_role_show_available_permissions",
     *  methods={"GET"},
     *  defaults={
     *        "_api_respond"=true,
     *        "_format"="json"
     *  }
     * )
     */
    public function showAvailablePermissions()
    {
        $role = new Role();

        return [
            'role_permissions' => $this->rolePermissionProvider->getResults($role),
            'tabs_options' => [
                'data' => $this->getTabListOptions()
            ],
            'capability_set_options' => [
                'data' => $this->rolePrivilegeCapabilityProvider->getCapabilities($role),
                'tab_ids' => $this->rolePrivilegeCategoryProvider->getTabList(),
                'readonly' => true
            ],
        ];
    }

    public function handle(Role $data, FormInterface $form)
    {
        $tabs = $this->rolePrivilegeCategoryProvider->getTabs();

        $decodedPrivileges = json_decode($form->get('privileges')->getData(), true);

        $this->entityManager->persist($data);

        $this->aclRoleHandler->processPrivileges($data, is_array($decodedPrivileges)? $decodedPrivileges: []);

        $this->entityManager->flush();

        return [
            'entity' => $data,
            'role_permissions' => $this->rolePermissionProvider->getResults($data),
            'tabs_options' => [
                'data' => $tabs
            ],
            'capability_set_options' => $this->rolePrivilegeCapabilityProvider->getCapabilitySetOptions($data),
            'privileges_config' => $this->userPrivileges,
            // TODO: it is a temporary solution. In a future it is planned to give an user a choose what to do:
            // completely delete a role and un-assign it from all users or reassign users to another role before
            'allow_delete' =>
                $data->getId() && !$this->roleRepository->hasAssignedUsers($data)
        ];
    }

    /**
     * @return array
     */
    protected function getTabListOptions()
    {
        return array_map(
            function (PrivilegeCategory $tab) {
                return [
                    'id' => $tab->getId(),
                    'label' => $tab->getLabel()
                ];
            },
            $this->rolePrivilegeCategoryProvider->getTabbedCategories()
        );
    }
}
