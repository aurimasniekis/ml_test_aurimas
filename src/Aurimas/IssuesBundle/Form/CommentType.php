<?php

namespace Aurimas\IssuesBundle\Form;

use Aurimas\IssuesBundle\Model\CommentModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * Class CommentType
 * @package Aurimas\IssuesBundle\Type
 * @author Aurimas Niekis <aurimas.niekis@gmail.com>
 */
class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var CommentModel $object */
        $object = $builder->getData();

        $builder->add('body', 'textarea');

        if ($object->getId()) {
            $builder->add('submit', 'submit', ['label' => 'Save']);
            $builder->add('cancel', 'button', ['label' => 'Cancel edit']);
        } else {
            $builder->add('submit', 'submit', ['label' => 'Comment']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Aurimas\\IssuesBundle\\Model\\CommentModel',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'aurimas_issue_comment';
    }

}
