<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class TodosType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userId = $options['data']['user']->getId();

        $builder
        ->add('title')
        ->add('content')
        ->add('dateCreate', DateTimeType::class, [
            'widget' => 'choice',
            'data' => new \DateTime("now"),
        ])
        ->add('dateModify', DateTimeType::class, [
            'widget' => 'choice',
            'data' => new \DateTime("now"),
        ])
        ->add('dateSheduled', DateTimeType::class, [
            'widget' => 'choice',
            'data' => new \DateTime("now + 30minutes"),
        ])
        ->add('status')
        ->add('category', EntityType::class, [
            'class' => 'AppBundle:Category',
            'query_builder' => function (EntityRepository $er) use ($userId) {
                return $er->createQueryBuilder('u')
                    ->where('u.user = :id')
                    ->setParameter('id', $userId)
                    ->orderBy('u.title', 'ASC');
            },
        ]);
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'user'       => null
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_todos';
    }


}
