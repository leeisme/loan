<?php

namespace App\Controller;

use App\Form\LoanType;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\LoanParameter;
use App\Service\LoanCalculator;
use Dompdf\Dompdf;

/**
 * @Route("/")
 */
class DefaultController extends AbstractController
{

	// TODO: Retrieve from datastore or algo
	const MAX_PMT_INCOME_RATIO = 0.15;
	const MIN_INCOME = 1000;
	
	/**
	 * Validate input common for loan information requests.
	 * @param array $data
	 * @param LoanParameter $loanParameterService
	 * @param LoanCalculator $loanCalculatorService
	 * @return array|string[]
	 * @throws Exception
	 */
	private function validateLoanParameters(
		array $data,
		LoanParameter $loanParameterService,
		LoanCalculator $loanCalculatorService
	): array 
	{
		$formErrors = [];
		$maxAmount = $loanParameterService->getMaxAmount($data['creditScore']);
		
		//  Process submitted data
		try {
			//  Check for requesting too large of an amount
			if ($data['amount'] > $maxAmount) {
				$formErrors[] = 'The maximum loan amount for your credit ' .
					'score is: $' . $maxAmount;
			}
		} catch (Exception $e) {
			$formErrors = ['Please check your inputs and resubmit the form'];
		}
		
		// Income must be at least x
		if (
			empty($formErrors) &&
			((float) $data['income'] < static::MIN_INCOME)
		) {
			$errMsg = sprintf('$%.2f', self::MIN_INCOME);
			$errMsg = 'Income must be at least ' . $errMsg;
			$formErrors[] = $errMsg;
		}
		
		// Payment cannot exceed % of monthly income.
		if (empty($formErrors)) {
			try {
				
				$fee = $loanParameterService->getOriginationFee($data['amount']);
				$income = $data['income'];
				$maxPmt = round(static::MAX_PMT_INCOME_RATIO * $income, 2);
				
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
					
					$formErrors[] = $errMsg;
				}
				
			} catch (Exception $e) {
				$formErrors = [
					'Please checkout your inputs and resubmit the form'
				];
			}
		}
		
		return $formErrors;
	}
	
	/**
	 * Build loanData used by views.
	 * @param LoanParameter $loanParameterService
	 * @param LoanCalculator $loanCalculatorService
	 * @param array $data
	 * @return array
	 */
	private function buildLoanData(
		LoanParameter $loanParameterService,
		LoanCalculator $loanCalculatorService,
		array $data
	): array
	{
		try {
			
			$interestRate = $loanParameterService->getInterestRate(
				$data['term'],
				$data['creditScore']
			);
			
			$fee = $loanParameterService->getOriginationFee($data['amount']);
			
			$payment = $loanCalculatorService->getMonthlyPayment(
				$data['amount'] + $fee,
				$interestRate,
				$data['term']
			);
			
			$paySchedule = $loanCalculatorService->getPaymentSchedule(
				$data['amount'] + $fee,
				$interestRate,
				$data['term']
			);
			
			$loanData['apr'] = // TODO: fix APR;
			$loanData['amount'] = number_format((float) $data['amount'], 2);
			$loanData['interestRate'] = $interestRate;
			$loanData['fee'] = $fee;
			$loanData['payment'] = $payment;
			$loanData['interest'] = $paySchedule['interest'];
			$loanData['schedule'] = $paySchedule['schedule'];
		} catch (Exception $e) {
			return [];
		}
		
		return $loanData;
	}
	
	/**
	 * @Route("/", name="home")
	 * @throws Exception
	 */
	public function home(
		Request $request, 
		LoanParameter $loanParameterService, 
		LoanCalculator $loanCalculatorService
	): Response
	{
		$form = $this->createForm(LoanType::class);
		$formErrors = [];
		$loanData = [];
		$data = [];
		
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			
			$formErrors = $this->validateLoanParameters(
				$data,
				$loanParameterService,
				$loanCalculatorService
			);
			
			if (empty($formErrors)) {
				$loanData = $this->buildLoanData(
					$loanParameterService,
					$loanCalculatorService,
					$data 
				);
				
				if (count($loanData) === 0) {
					$formErrors = [
						'Please checkout your inputs and resubmit the form'
					];
				}
			}
		}
		
		if (isset($data['asPdf']) && $data['asPdf'] === '1') {
			$loanData['printedDate'] = (new DateTime())->format('m-d-Y'); 
			$loanData['title'] = 'Loan Payment Schedule';
			
			$html = $this->renderView('partials/schedule.html.twig', 
				['loanData' => $loanData]
			);
			
			$pdfOptions = ['defaultFont' => 'Arial'];
			$domPdf = new Dompdf($pdfOptions);
			$domPdf->loadHtml($html);
			$domPdf->render();
			$domPdf->stream("payment_schedule.pdf", ["Attachment" => false]);
		} else {
			return $this->render(
				'index.html.twig',
				[
					'form'       => $form->createView(),
					'formErrors' => $formErrors,
					'loanData'   => $loanData,
				]
			);
		}
	}
}
