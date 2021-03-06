<?php

namespace Armensa\BaseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class VehiculoType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('marca', 'text', array('required'=>true, 'invalid_message' => 'Necesitamos la Marca'))
            ->add('modelo', 'integer')
            ->add('placa')
            ->add('tipoVehiculo')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Armensa\BaseBundle\Entity\Vehiculo'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'armensa_basebundle_vehiculo';
    }
}
