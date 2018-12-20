<?php

declare(strict_types=1);

namespace Pintushi\Bundle\UserBundle\Handler;

use Pintushi\Bundle\UserBundle\Request\ChangePhoneNumber;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;
use Pintushi\Bundle\UserBundle\Repository\UserRepository;
use Pintushi\ShopApiPlugin\Handler\HandlerInterface;
use Pintushi\Bundle\UserBundle\Entity\User;

final class ChangePhoneNumberHandler implements HandlerInterface
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

    public function handle(ChangePhoneNumber $command)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        Assert::isInstanceOf($user, User::class);

        $user->setPhoneNumber($command->getPhoneNumber());

        $this->userRepository->add($user);

        return $user;
    }
}
