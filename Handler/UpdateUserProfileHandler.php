<?php

declare(strict_types=1);

namespace Pintushi\Bundle\UserBundle\Handler;

use Pintushi\Bundle\UserBundle\Request\UpdateUserProfile;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;
use Pintushi\Bundle\UserBundle\Repository\UserRepository;
use Pintushi\ShopApiPlugin\Handler\HandlerInterface;
use Pintushi\Bundle\UserBundle\Entity\User;

final class UpdateUserProfileHandler implements HandlerInterface
{
    private $userRepository;
    private $tokenStorage;

    public function __construct(
        UserRepository $userRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->userRepository = $userRepository;
        $this->tokenStorage = $tokenStorage;
    }

    public function handle(UpdateUserProfile $command)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        Assert::isInstanceOf($user, User::class);

        $user->setFirstName($command->getFirstName());
        $user->setLastName($command->getLastName());
        $user->setAvatar($command->getAvatar());


        $this->userRepository->add($user);

        return $user;
    }
}
