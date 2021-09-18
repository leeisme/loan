<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class LoanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
	    $builder
		    ->add('amount', MoneyType::class, [
		    	'currency' => 'usd',
		    	'html5' => true,
		    	'attr' => [
		    		'min' => 1000,
				    'max' => 75000,
				    'step' => 1,
			    ],
	            //  Whole dollars only
	            'scale' => 0,
			    'invalid_message' => 'Please Enter a value Between $1,000 and $75,000',
		    ])
		    ->add('term', ChoiceType::class, [
			    'choices'  => [
				    '12 Months' => 12,
				    '18 Months' => 18,
				    '24 Months' => 24,
				    '36 Months' => 36,
				    '48 Months' => 48,
				    '60 Months' => 60,
			    ],
		    ])
			->add('income', MoneyType::class, [
				'currency' => 'usd',
				'html5' => true,
				'attr' => [
					'min' => 1000,
					'max' => 50000,
					'step' => 500
				],
			])
		    ->add('creditScore', ChoiceType::class, [
		    	//  In a real application these options would come from either the database or another component, for the purpose of this exercise repeating them helps to keep the scope limited 
			    'choices'  => [
				    'Poor (350-629)' => 'poor',
				    'Average (630-689)' => 'average',
				    'Good (690-719)' => 'good',
				    'Excellent (720-850)' => 'excellent',
			    ],
		    ])
		    ->add('submit', SubmitType::class, [
		    	'label' => 'Submit'
		    ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
