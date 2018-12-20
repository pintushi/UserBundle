<?php

declare(strict_types=1);

namespace Pintushi\Bundle\UserBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\User\UserInterface;
use Pintushi\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

use Pintushi\Bundle\UserBundle\Password\PasswordUpdaterInterface;
use Pintushi\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Pintushi\Bundle\SecurityBundle\Exception\ForbiddenException;

class SelfDeleteListener
{
    private $tokenAccessor;

    public function __construct(TokenAccessorInterface $tokenAccessor)
    {
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function preRemove(LifecycleEventArgs $event): void
    {
        $entity = $event->getEntity();

        if ($entity instanceof UserInterface && $this->tokenAccessor->getUser()->getId() == $entity->getId()) {
            throw new  ForbiddenException('You can\'t delete yourself');
        }

        if ($entity instanceof OrganizationInterface && $this->tokenAccessor->getOrganization()->getId() == $entity->getId()) {
            throw new ForbiddenException('You are not allowed to delete the organization you are in');
        }
    }
}
