<?php

namespace Pintushi\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Pintushi\Bundle\UserBundle\Entity\UserInterface;
use Pintushi\Bundle\UserBundle\Entity\Role;
use Pintushi\Bundle\UserBundle\Entity\Group;
use Pintushi\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Pintushi\Bundle\OrganizationBundle\Form\Type\OrganizationsSelectType;
use Doctrine\ORM\EntityRepository;
use Videni\Bundle\RestBundle\Form\Type\AbstractResourceType;

class UserType extends AbstractResourceType
{
    private $authorizationChecker;

    public function __construct(string $dataClass, array $validationGroups = [], AuthorizationCheckerInterface $authorizationChecker)
    {
        parent::__construct($dataClass, $validationGroups);

        $this->authorizationChecker = $authorizationChecker;
    }

     /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $this->setDefaultUserFields($builder);

        if ($this->authorizationChecker->isGranted('pintushi_user_role_view')) {
            $builder->add(
                'roles',
                EntityType::class,
                [
                    'label'         => 'pintushi.user.roles.label',
                    'class'         => Role::class,
                    'choice_label'      => 'label',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                            ->where('r.role <> :anon')
                            ->setParameter('anon', UserInterface::ROLE_ANONYMOUS)
                            ->orderBy('r.label');
                    },
                    'multiple'      => true,
                    'expanded'      => true,
                    'required'      => true,
                ]
            );
        }
        if ($this->authorizationChecker->isGranted('pintushi_user_group_view')) {
            $builder->add(
                'groups',
                EntityType::class,
                [
                    'label'     => 'pintushi.user.groups.label',
                    'class'     => Group::class,
                    'choice_label'  => 'name',
                    'multiple'  => true,
                    'expanded'  => true,
                    'required'  => false,
                ]
            );
        }

        $this->addOrganizationField($builder);
    }

    protected function setDefaultUserFields(FormBuilderInterface $builder)
    {
        $builder
            ->add('username', FormTypes\TextType::class)
            ->add('email', FormTypes\EmailType::class)
            ->add('avatar', FormTypes\TextType::class)
            ->add('plainPassword', FormTypes\PasswordType::class)
            ->add('gender', FormTypes\ChoiceType::class, [
                'choices'=>[
                    '男' => UserInterface::MALE_GENDER,
                    '女' => UserInterface::FEMALE_GENDER,
                    '未知' => UserInterface::UNKNOWN_GENDER,
                ],
            ])
            ->add('phoneNumber', FormTypes\TextType::class)
            ->add('firstName', FormTypes\TextType::class)
            ->add('lastName', FormTypes\TextType::class)
            ->add('enabled', FormTypes\CheckboxType::class)
            ->add('expiresAt', FormTypes\DateTimeType::class, [
                'widget'=>'single_text',
            ])
        ;
    }

    /**
     * @param FormBuilderInterface $builder
     */
    protected function addOrganizationField(FormBuilderInterface $builder)
    {
        if ($this->authorizationChecker->isGranted('pintushi_organization_view')
            && $this->authorizationChecker->isGranted('pintushi_business_unit_view')
        ) {
            $builder->add(
                'organizations',
                OrganizationsSelectType::class,
                [
                    'required' => false,
                ]
            );
        }
    }
}
