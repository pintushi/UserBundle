<?php

declare(strict_types=1);

namespace Pintushi\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
           ->add(
                'name',
                FormTypes\TextType::class,
                array(
                    'label' => 'pintushi.user.group.name.label',
                    'required' => true,
                )
            )
        ;
    }
}
