<?php

namespace Pintushi\Bundle\UserBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Pintushi\Bundle\UserBundle\Entity\User;
use Pintushi\Bundle\UserBundle\Entity\Role;
use Pintushi\Bundle\UserBundle\Repository\RoleRepository;
use Pintushi\Bundle\OrganizationBundle\Repository\BusinessUnitRepository;
use Pintushi\Bundle\OrganizationBundle\DataFixtures\OrganizationFixture;

class UserFixture extends Fixture implements DependentFixtureInterface
{
    const DEFAULT_ADMIN_USERNAME = 'admin';
    const DEFAULT_ADMIN_PASSWORD = '123456';
    const DEFAULT_ADMIN_EMAIL = 'admin@example.com';

    private $users = [
        [
            'username' => self::DEFAULT_ADMIN_USERNAME,
            'password' => self::DEFAULT_ADMIN_PASSWORD,
            'phone_number' => '18382388314',
            'email' => self::DEFAULT_ADMIN_EMAIL,
            'role' => RoleFixture::ROLE_ADMINISTRATOR,
            'access' => [
                'org_ref' => 'main_organization',
                'bu_ref' => 'main_business_unit',
            ],
        ],
        [
            'username' => 'organization',
            'password' => self::DEFAULT_ADMIN_PASSWORD,
            'phone_number' => '18382388315',
            'email' => 'supplier@example.com',
            'role' => RoleFixture::ROLE_ORGANIZATION,
            'access' => [
                'org_ref' => 'second_organization',
                'bu_ref' => 'second_business_unit',
            ],
        ]
    ];

    private $userManager;

    private $roleRepository;

    private $businessUnitRepository;

    public function __construct(
        ObjectManager $userManager,
        RoleRepository $roleRepository,
        BusinessUnitRepository $businessUnitRepository
    ) {
        $this->userManager = $userManager;
        $this->roleRepository = $roleRepository;
        $this->businessUnitRepository = $businessUnitRepository;
    }

    public function load(ObjectManager $manager)
    {
        foreach ($this->users as $user) {
            $this->create($user);
        }

        $this->userManager->flush();
    }

    public function create($user)
    {
        $role = $this->roleRepository
            ->findOneBy(['role' => $user['role']]);

        if (!$role) {
            throw new \RuntimeException('Administrator role should exist.');
        }

        if ($this->isUserWithRoleExist($role)) {
            return;
        }

        $businessUnit = $this->getReference($user['access']['bu_ref']);
        $organization = $this->getReference($user['access']['org_ref']);

        $adminUser = new User();

        $adminUser
            ->setUsername($user['username'])
            ->setPhoneNumber($user['phone_number'])
            ->setEmail($user['email'])
            ->setOwner($businessUnit)
            ->setPlainPassword(self::DEFAULT_ADMIN_PASSWORD)
            ->addRole($role)
            ->addBusinessUnit($businessUnit)
            ->setOrganization($organization)
            ->setEnabled(true)
            ;

        $this->userManager->persist($adminUser);
    }

    /**
     * @param ObjectManager $manager
     * @param Role $role
     * @return bool
     */
    protected function isUserWithRoleExist(Role $role)
    {
        return null !== $this->roleRepository->getFirstMatchedUser($role);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [RoleFixture::class];
    }
}
