<?php

namespace Pintushi\Bundle\UserBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Pintushi\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pintushi\Bundle\UserBundle\Entity\Role;
use Pintushi\Bundle\UserBundle\Form\Handler\AclRoleHandler;
use Pintushi\Bundle\UserBundle\Model\PrivilegeCategory;
use Pintushi\Bundle\UserBundle\Provider\Privilege\RolePrivilegeCapabilityProvider;
use Pintushi\Bundle\UserBundle\Provider\Privilege\RolePrivilegeCategoryProvider;
use Pintushi\Bundle\UserBundle\Repository\RoleRepository;
use Pintushi\Bundle\UserBundle\Provider\Privilege\RolePermissionProvider;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;
use Pintushi\Bundle\UserBundle\Form\Type\AclRoleType;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Pintushi\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Limenius\Liform\Liform;

class RoleController extends AbstractController
{
    private $rolePrivilegeCapabilityProvider;

    private $rolePrivilegeCategoryProvider;

    private $aclRoleHandler;

    private $roleRepository;

    private $userPrivileges;

    private $rolePermissionProvider;

    private $entityManager;

    private $formFactory;

    private $tokenAccessor;

    private $serializer;

    private $liform;

    public function __construct(
        RolePrivilegeCapabilityProvider $rolePrivilegeCapabilityProvider,
        RolePrivilegeCategoryProvider $rolePrivilegeCategoryProvider,
        AclRoleHandler $aclRoleHandler,
        RoleRepository $roleRepository,
        RolePermissionProvider $rolePermissionProvider,
        ObjectManager $entityManager,
        FormFactoryInterface $formFactory,
        TokenAccessorInterface $tokenAccessor,
        SerializerInterface $serializer,
        Liform $liform,
        $userPrivileges
    ) {
        $this->rolePrivilegeCapabilityProvider = $rolePrivilegeCapabilityProvider;
        $this->rolePrivilegeCategoryProvider = $rolePrivilegeCategoryProvider;
        $this->aclRoleHandler = $aclRoleHandler;
        $this->roleRepository = $roleRepository;
        $this->rolePermissionProvider = $rolePermissionProvider;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->tokenAccessor = $tokenAccessor;
        $this->serializer = $serializer;
        $this->liform = $liform;
        $this->userPrivileges = $userPrivileges;
    }

    /**
     * @AclAncestor("pintushi_role_create")
     *
     * @Route("/roles/create",
     *  name="api_role_create",
     *  methods={"POST","GET"},
     *  defaults={
     *      "_format"="json"
     *  }
     * )
     */
    public function create(Request $request)
    {
        $role = new Role();

        return $this->handle($role, $request);
    }

    /**
     * @Route("roles/{id}",
     *     name="api_role_view",
     *     requirements={"id"="\d+"},
     *     methods={"GET"},
     *     defaults={
     *         "_api_resource_class"=Role::class,
     *         "_api_operation_name"="view",
     *    }
     *  )
     *
     * @return array
     */
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
     *  @AclAncestor("pintushi_role_update")
     *
     * @Route("/roles/{id}/update",
     *      name="api_role_update",
     *      requirements={"id"="\d+"},
     *      methods={"POST", "GET"},
     *      defaults={
     *         "id"=0
     *      }
     * )
     *
     * @return array
     */
    public function update(Role $role, Request $request)
    {
        $currentOrganization = $this->tokenAccessor->getOrganization();
        if ($currentOrganization->isGlobal() ||
            null !== $role->getOrganization() &&
            $currentOrganization === $role->getOrganization()
        ) {
            return $this->handle($role, $request);
        }else {
            throw $this->createAccessDeniedException();
        }
    }

    /**
     * @param Role $role
     *
     */
    protected function handle(Role $role, Request $request)
    {
       $form = $this->formFactory->createNamed(
            '',
            AclRoleType::class,
            $role,
            [
                'validation_groups' => ['pintushi'],
                'csrf_protection' => false,
            ]
        );

        $tabs = $this->rolePrivilegeCategoryProvider->getTabs();

        $data = [
            'form_schema'  => $this->liform->transform($form),
            'initial_values' => $form->createView(),
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

        $context = new SerializationContext();
        $context
            ->setAttribute('form', $form)
            ->setGroups(['Role'])
        ;

        $status = Response::HTTP_OK;

        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true)) {
            $form->submit($request->request->all(), false);

            if ($form->isValid()) {
                $decodedPrivileges = json_decode($form->get('privileges')->getData(), true);
                $this->entityManager->persist($role);
                $this->aclRoleHandler->processPrivileges($role, is_array($decodedPrivileges)? $decodedPrivileges: []);

                $this->entityManager->flush();
            }else {
                $context->setAttribute('status_code', Response::HTTP_BAD_REQUEST);
                $status = Response::HTTP_BAD_REQUEST;

                $data['form'] = $form;
            }
        }

        return new Response(
            $this->serializer->serialize($data, $request->getRequestFormat(), $context),
            $status,
            [
                'Content-Type' => sprintf('%s; charset=utf-8', $request->getMimeType($request->getRequestFormat())),
                'Vary' => 'Accept',
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'deny',
            ]);
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
