<?php

declare(strict_types=1);

namespace Pintushi\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Pintushi\Bundle\UserBundle\Entity\UserInterface;

abstract class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', FormTypes\TextType::class)
            ->add('email', FormTypes\EmailType::class)
            ->add('plainPassword', FormTypes\PasswordType::class)
            ->add('enabled', FormTypes\CheckboxType::class)
            ->add('gender', FormTypes\ChoiceType::class, [
                'choices'=>[
                    '男' => UserInterface::MALE_GENDER,
                    '女' => UserInterface::FEMALE_GENDER,
                    '未知' => UserInterface::UNKNOWN_GENDER,
                ],
            ])
        ;
    }
}
