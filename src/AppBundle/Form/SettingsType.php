<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SettingsType extends AbstractType
{
    public $container;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->container = $options['container'];
        $app_settings = $this->container;

        $builder
        ->add('name')
        ->add('value', ChoiceType::class, [
            'choices' => $app_settings['locales'],
            'choice_label' => function ($choice) {
                return $choice;
            },
            'choice_value' => function ($choice) {
                return $choice;
            }
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Settings',
            'container' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'appbundle_settings';
    }
}
