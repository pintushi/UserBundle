<?php

namespace Pintushi\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Pintushi\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Pintushi\Bundle\UserBundle\Form\EventListener\ChangeRoleSubscriber;
use Pintushi\Bundle\UserBundle\Entity\Role;

class AclRoleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add(
            'label',
            TextType::class,
            [
                'required' => true,
                'label'    => 'pintushi.user.role.role.label'
            ]
        )
        ->add('description', TextType::class)
        ->add(
            'privileges',
            HiddenType::class,
            [
                'mapped' => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Role::class,
                'csrf_token_id' => 'role',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pintushi_user_role_form';
    }
}
