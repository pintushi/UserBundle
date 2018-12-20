<?php

namespace Pintushi\Bundle\UserBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Pintushi\Bundle\UserBundle\Entity\Role;

class RoleFixture extends Fixture
{
    const ROLE_ANONYMOUS     = 'IS_AUTHENTICATED_ANONYMOUSLY';
    const ROLE_USER          = 'ROLE_USER';
    const ROLE_ADMINISTRATOR = 'ROLE_ADMINISTRATOR';
    const ROLE_MANAGER       = 'ROLE_MANAGER';
    const ROLE_ORGANIZATION  = 'ROLE_ORGANIZATION';

    /**
     * Load roles
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $roleAnonymous = new Role(self::ROLE_ANONYMOUS);
        $roleAnonymous->setLabel('Anonymous');

        $roleUser = new Role(self::ROLE_USER);
        $roleUser->setLabel('User');

        //超级管理员
        $roleAdmin = new Role(self::ROLE_ADMINISTRATOR);
        $roleAdmin->setLabel('Administrator');

        //管理员
        $roleManager = new Role(self::ROLE_MANAGER);
        $roleManager->setLabel('Manager');

         //组织管理员
        $roleOrganizationAdmin = new Role(self::ROLE_ORGANIZATION);
        $roleOrganizationAdmin->setLabel('组织管理员');

        $manager->persist($roleAnonymous);
        $manager->persist($roleUser);
        $manager->persist($roleAdmin);
        $manager->persist($roleManager);
        $manager->persist($roleOrganizationAdmin);

        $manager->flush();
    }
}
