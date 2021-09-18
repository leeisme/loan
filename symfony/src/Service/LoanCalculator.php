<?php

namespace App\Service;

use Exception;

class LoanCalculator
{
	
	/**
	 * @param  int  $principal
	 * @param  float  $interestRate
	 * @param  int  $term
	 *
	 * @return float
	 * @throws Exception
	 */
	public function getMonthlyPayment(int $principal, float $interestRate, int $term): float
	{
		//	Check for valid term
		if ($term < 0) {
			throw new Exception('invalid term');
		}
		
		if ($interestRate < 0) {
			throw new Exception('invalid Interest rate');
		}
		
		if ($principal < 0) {
			throw new Exception('invalid principal amount');
		}
		
		//  special case when full principal needs would be due immediately
		// (prevents division by zero)
		if ($term == 0) {
			return $principal;
		}
		
		if ($interestRate > 0) {
			//	Get equivalent monthly interest rate as a multiplier
			$monthlyInterestRate = $interestRate / (12 * 100);
			
			$monthly_payment = $principal * ($monthlyInterestRate / (1 - pow((1 + $monthlyInterestRate), -$term)));
		} else {
			$monthly_payment = $principal / $term;
		}
		
		return round($monthly_payment, 2);
	}
	
	/**
	 * Calculate amortization schedule and return as array
	 * @param float $loanAmount Total amount financed
	 * @param float $interestRate Interest rate for the loan (15.00, 25.50...)
	 * @param int $termInMonths Number of months for the loan
	 * @param int $termIntervals Payment terms
	 * @return array
	 * TODO Add support for zero (0) interest rate. Function now fails with 0%.
	 */
	public function getPaymentSchedule(
		float $loanAmount,
		float $interestRate,
		int $termInMonths,
		int $termIntervals = 12
	): array {
		$schedule = [];
		$i = 1;
		$period = $termInMonths;
		$interestRate = ($interestRate / 100) / $termIntervals;
		
		while ($i <= $termInMonths) {
			$pmtAmount =  (1 - pow((1 + $interestRate), -$period));
			$termPay = ($loanAmount * $interestRate) / $pmtAmount;
			$interest = $loanAmount * $interestRate;
			$principal = $termPay - $interest;
			$balance = $loanAmount - $principal;
			
			$schedule[] = [
				'payment' 	=> round($termPay, 2),
				'interest' 	=> round($interest, 2),
				'principal' => round($principal, 2),
				'balance' 	=> round($balance, 2)
			];
			
			$loanAmount = $balance;
			$period--;
			$i++;
		}
		
		return $schedule;
	}
}
