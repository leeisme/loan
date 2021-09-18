<?php

namespace App\Controller;

use App\Form\LoanType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\LoanParameter;
use App\Service\LoanCalculator;

/**
 * @Route("/")
 */
class DefaultController extends AbstractController
{

	/** @var float Typically retrieved from datastore or algo */
	const MAX_PMT_INCOME_RATIO = 0.15;
	
	/**
	 * @Route("/", name="home")
	 */
	public function home(Request $request, LoanParameter $loanParameterService, LoanCalculator $loanCalculatorService): Response
	{
		$form = $this->createForm(LoanType::class);
		
		$formErrors = [];
		$loanData = [];
		
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			
			//  Process submitted data
			try {
				//  Check for requesting too large of an amount
				if ($data['amount'] > $loanParameterService->getMaxAmount($data['creditScore'])) {
					$formErrors[] = 'The maximum loan amount for your credit score is: $'.$loanParameterService->getMaxAmount($data['creditScore']);
				}
			} catch (\Exception $e) {
				$formErrors = ['Please check your inputs and resubmit the form'];
			}
			
			// Payment cannot exceed % of monthly income.
			if (empty($formErrors)) {
				try {
					
					$fee = $loanParameterService->getOriginationFee($data['amount']);
					$income = $data['income'];
					$maxPmt = round(static::MAX_PMT_INCOME_RATIO * $income);
					
					$interestRate = $loanParameterService->getInterestRate(
						$data['term'], 
						$data['creditScore']
					);
					
					$payment = $loanCalculatorService->getMonthlyPayment(
						$data['amount'] + $fee, 
						$interestRate, 
						$data['term']
					);
					
					if ($payment > $maxPmt) {
						$fmtPayment = sprintf('$%.2f', $payment);
						$fmtMaxPayment = sprintf('$%.2f', $maxPmt);
						$fmtPercent = sprintf('%.2f%%', static::MAX_PMT_INCOME_RATIO * 100);
						
						$errMsg = "The payment amount of {$fmtPayment} calculated " .
							"for this loan exceeds {$fmtPercent} of monthly " .
							"income ({$fmtMaxPayment})";
						
						$formErrors = [$errMsg];
					}
					
					
				} catch (\Exception $e) {
					$formErrors = [
						'Please checkout your inputs and resubmit the form'
					];
				}
			}
			
			if (empty($formErrors)) {
				try {
					$interestRate = $loanParameterService->getInterestRate($data['term'], $data['creditScore']);
					$fee = $loanParameterService->getOriginationFee($data['amount']);
					$payment = $loanCalculatorService->getMonthlyPayment($data['amount'] + $fee, $interestRate, $data['term']);
					
					$loanData['interestRate'] = $interestRate;
					$loanData['fee'] = $fee;
					$loanData['payment'] = $payment;
				} catch (\Exception $e) {
					$formErrors = ['Please check your inputs and resubmit the form'];
				}
			}
		}
		
		return $this->render(
			'index.html.twig',
			[
				'form'       => $form->createView(),
				'formErrors' => $formErrors,
				'loanData'   => $loanData,
				'loanSchedule' => [],
			]
		);
	}
	
	
}
