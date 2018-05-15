<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TodosType extends AbstractType
{

    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $builder
        ->add('title')
        ->add('content', TextareaType::class)
        ->add('dateCreate')
        ->add('dateModify')
        ->add('dateSheduled')
        ->add('status', ChoiceType::class, [
            'choices' => [0,1],
            'expanded' => true
        ])
        ->add('category', EntityType::class, [
            'class' => 'AppBundle:Category',
            'query_builder' => function (EntityRepository $er) use ($user) {
                return $er
                ->createQueryBuilder('u')
                ->where('u.user = :id')
                ->setParameter('id', $user->getId())
                ->orderBy('u.title', 'ASC');
            },
        ]);
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Todos'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_todos';
    }


}
