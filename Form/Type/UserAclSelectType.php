<?php
namespace Pintushi\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Pintushi\Bundle\FormBundle\Form\Type\SelectHiddenAutocompleteType;

class UserAclSelectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'configs'            => [
                    'placeholder'             => 'pintushi.user.form.choose_user',
                    'result_template'         => 'user-autocomplete-result',
                    'selection_template'      => 'user-autocomplete-selection',
                    'component'               => 'acl-user-autocomplete',
                    'data_class_name'         => '',
                    'permission'              => 'CREATE',
                ],
                'autocomplete_alias' => 'acl_users',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return SelectHiddenAutocompleteType::class;
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
        return 'pintushi_user_acl_select';
    }
}
