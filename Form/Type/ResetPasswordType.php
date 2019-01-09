<?php

declare(strict_types=1);

namespace Pintushi\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;

final class ResetPasswordType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', FormTypes\RepeatedType::class, [
                'type' => FormTypes\PasswordType::class,
            ])
            ->add('phoneNumber', FormTypes\TextType::class)
            ->add('code', FormTypes\TextType::class)
        ;
    }
}
