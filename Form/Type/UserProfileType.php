<?php

namespace Pintushi\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Pintushi\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Videni\Bundle\RestBundle\Form\Type\AbstractResourceType;

class UserProfileType extends AbstractResourceType
{
     /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('avatar', FormTypes\TextType::class)
            ->add('firstName', FormTypes\TextType::class)
            ->add('lastName', FormTypes\TextType::class)
            ->add('gender', FormTypes\ChoiceType::class, [
                'choices'=>[
                    '男' => UserInterface::MALE_GENDER,
                    '女' => UserInterface::FEMALE_GENDER,
                    '未知' => UserInterface::UNKNOWN_GENDER,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
         $resolver->setDefaults(
            [
                'ownership_disabled' => true,
            ]
        );
    }
}
