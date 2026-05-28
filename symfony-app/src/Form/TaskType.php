<?php

namespace App\Form;

use App\Entity\Folder;
use App\Entity\Priority;
use App\Entity\Task;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('isPinned', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('status')
            ->add('folder', EntityType::class, [
                'class' => Folder::class,
                'query_builder' => function (EntityRepository $er): QueryBuilder{
                    return $er->createQueryBuilder('f')
                        ->orderBy('f.id', 'ASC');
                },
                'choice_label' => 'name'
            ])
            ->add('priority', EntityType::class, [
                'class' => Priority::class,
                'query_builder' => function (EntityRepository $er): QueryBuilder{
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.name', 'ASC');
                },
                'choice_label' => 'name'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
