<?php

namespace Pintushi\Bundle\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Pintushi\Bundle\SecurityBundle\Annotation\Acl;
use Pintushi\Bundle\UserBundle\Entity\Role;
use Pintushi\Bundle\UserBundle\Form\Handler\AclRoleHandler;
use Pintushi\Bundle\UserBundle\Model\PrivilegeCategory;
use Pintushi\Bundle\UserBundle\Provider\Privilege\RolePrivilegeCapabilityProvider;
use Pintushi\Bundle\UserBundle\Provider\Privilege\RolePrivilegeCategoryProvider;
use Pintushi\Bundle\UserBundle\Repository\RoleRepository;
use Pintushi\Bundle\UserBundle\Provider\Privilege\RolePermissionProvider;

class RoleController extends Controller
{
    private $rolePrivilegeCapabilityProvider;
    private $rolePrivilegeCategoryProvider;
    private $aclRoleHandler;
    private $roleRepository;
    private $userPrivileges;
    private $rolePermissionProvider;

    public function __construct(
        RolePrivilegeCapabilityProvider $rolePrivilegeCapabilityProvider,
        RolePrivilegeCategoryProvider $rolePrivilegeCategoryProvider,
        AclRoleHandler $aclRoleHandler,
        RoleRepository $roleRepository,
        RolePermissionProvider $rolePermissionProvider,
        $userPrivileges
    ) {
        $this->rolePrivilegeCapabilityProvider = $rolePrivilegeCapabilityProvider;
        $this->rolePrivilegeCategoryProvider = $rolePrivilegeCategoryProvider;
        $this->aclRoleHandler = $aclRoleHandler;
        $this->roleRepository = $roleRepository;
        $this->rolePermissionProvider = $rolePermissionProvider;
        $this->userPrivileges = $userPrivileges;
    }

    /**
     * @Acl(
     *      id="pintushi_user_role_create",
     *      type="entity",
     *      class="PintushiUserBundle:Role",
     *      permission="CREATE"
     * )
     * @Route("roles",
     *  name="pintushi_user_role_create",
     *  methods={"POST"},
     *  defaults={
     *        "_api_receive"=false,
     *        "_api_resource_class"=Role::class,
     *        "_api_collection_operation_name"="post",
     *  }
     * )
     */
    public function create()
    {
        return $this->handle(new Role());
    }

    /**
     * @Route("roles/{id}",
     *     name="pintushi_user_role_view",
     *     requirements={"id"="\d+"},
     *     methods={"GET"},
     *     defaults={
     *        "_api_receive"=true,
     *        "_api_resource_class"=Role::class,
     *        "_api_item_operation_name"="get",
     *     }
     *  )
     *
     * @Acl(
     *      id="pintushi_user_role_view",
     *      type="entity",
     *      class="PintushiUserBundle:Role",
     *      permission="VIEW"
     * )
     *
     * @param Role $role
     *
     * @return array
     */
    public function view($data)
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
     * @Acl(
     *      id="pintushi_user_role_update",
     *      type="entity",
     *      class="PintushiUserBundle:Role",
     *      permission="EDIT"
     * )
     * @Route("/roles/{id}",
     *      name="pintushi_user_role_update",
     *      requirements={"id"="\d+"},
     *      methods={"POST"},
     *      defaults={
     *         "id"=0,
     *         "_api_receive"=false,
     *         "_api_resource_class"=Role::class,
     *         "_api_item_operation_name"="put",
     *      }
     * )
     *
     * @param Role $entity
     * @ParamConverter("post", options={"id" = "id"})
     * @return array
     */
    public function update(Role $role)
    {
        return $this->handle($role);
    }

    /**
     * @param Role $role
     *
     */
    protected function handle(Role $role)
    {
        /** @var AclRoleHandler $aclRoleHandler */
        $aclRoleHandler = $this->aclRoleHandler;
        $aclRoleHandler->createForm($role);

        if ($aclRoleHandler->process($role)) {
            return $role;
        }

        $form = $aclRoleHandler->createView();
        $tabs = $this->rolePrivilegeCategoryProvider->getTabs();

        return [
            'entity' => $role,
            'role_permissions' => $this->rolePermissionProvider->getResults($role),
            'tabs_options' => [
                'data' => $tabs
            ],
            'capability_set_options' => $this->rolePrivilegeCapabilityProvider->getCapabilitySetOptions($role),
            'privileges_config' => $this->userPrivileges,
            // TODO: it is a temporary solution. In a future it is planned to give an user a choose what to do:
            // completely delete a role and un-assign it from all users or reassign users to another role before
            'allow_delete' =>
                $role->getId() && !$this->roleRepository->hasAssignedUsers($role)
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
                    'label' => $this->get('translator')->trans($tab->getLabel())
                ];
            },
            $this->rolePrivilegeCategoryProvider->getTabbedCategories()
        );
    }
}
