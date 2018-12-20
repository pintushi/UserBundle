<?php

namespace Pintushi\Bundle\UserBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Pintushi\Bundle\UserBundle\Entity\User;
use League\Tactician\CommandBus;

class ShowUserProfile
{
    private $entityManager;

    private $tokenStorage;

    public function __construct(
        ObjectManager $entityManager,
        TokenStorageInterface  $tokenStorage
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Route(
     *     name="api_admin_show_user_profile",
     *     path="/profile",
     *     methods={"GET"},
     *     options={"expose"=true},
     *     defaults={
     *         "_api_receive"=false,
     *         "_api_resource_class"=User::class,
     *         "_api_item_operation_name"="get_user_profile",
     *     }
     * )
     */
    public function __invoke(Request $request)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $this->entityManager->refresh($user);

        return $user;
    }
}
