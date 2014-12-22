<?php

namespace Aurimas\IssuesBundle\Form;

use Aurimas\IssuesBundle\Model\IssueModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class IssueType
 * @package Aurimas\IssuesBundle\Form
 * @author Aurimas Niekis <aurimas.niekis@gmail.com>
 */
class IssueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var IssueModel $object */
        $object = $builder->getData();

        $builder->add('title', 'text');
        $builder->add('body', 'textarea');

        if ($object->getId()) {
            $builder->add('submit', 'submit', ['label' => 'Save']);
            $builder->add('cancel', 'button', ['label' => 'Cancel edit']);
        } else {
            $builder->add('submit', 'submit', ['label' => 'Create']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Aurimas\\IssuesBundle\\Model\\IssueModel',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'aurimas_issue';
    }
}
