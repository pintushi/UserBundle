<?php

namespace Pintushi\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Pintushi\Bundle\UserBundle\Entity\Role;
use Pintushi\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Videni\Bundle\RestBundle\Form\DataTransformer\EntityToIdTransformer;
use Pintushi\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Pintushi\Bundle\SecurityBundle\ORM\DoctrineHelper;
use Symfony\Component\Validator\Constraints\NotBlank;
use Pintushi\Bundle\OrganizationBundle\Form\EventListener\OwnerFormSubscriber;
use Pintushi\Bundle\OrganizationBundle\Form\Type\OrganizationSelectType;

class AclRoleType extends AbstractType
{
    private $tokenAccessor;
    private $authorizationChecker;
    private $doctrineHelper;

    public function __construct(
        TokenAccessorInterface $tokenAccessor,
        AuthorizationCheckerInterface $authorizationChecker,
        DoctrineHelper $doctrineHelper
    ) {
        $this->tokenAccessor = $tokenAccessor;
        $this->authorizationChecker = $authorizationChecker;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add( 'label', TextType::class)
            ->add('description', TextType::class)
            ->add('privileges', HiddenType::class,  [
                    'mapped' => false,
                ]
            );

        $user = $this->tokenAccessor->getUser();
        if (!$user) {
            return;
        }

        $organization = $this->tokenAccessor->getOrganization();

        if ($this->authorizationChecker->isGranted('VIEW', 'entity:'. Organization::class)) {
            $builder->add('organization', OrganizationSelectType::class);
        } else {
            $builder->add('organization', EntityType::class, [
                'class'                => Organization::class,
                'property'             => 'name',
                'query_builder'        => function (OrgnaizationRepository $repository) use ($user) {
                    $qb = $repository->createQueryBuilder('o');
                    $qb->andWhere($qb->expr()->isMemberOf(':user', 'o.users'));
                    $qb->setParameter('user', $user);

                    return $qb;
                },
                'mapped'               => true,
            ]);
        }

        $isAssignGranted = $this->authorizationChecker->isGranted('ASSIGN', 'entity:'. Role::Class);

         $builder->addEventSubscriber(
            new OwnerFormSubscriber(
                $this->doctrineHelper,
                'organization',
                'pintushi.organiation.organization.label',
                $isAssignGranted,
                $this->tokenAccessor->getOrganization()
            )
        );

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit'], 128);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Role::class,
            ]
        );
    }

     /**
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $data = $event->getForm()->getData();

        $userOrganization = $this->tokenAccessor->getOrganization();
        if(!$userOrganization->isGlobal() && !$data->getOrganization()){
            $data->setOrganization($userOrganization);
        }
    }
}
