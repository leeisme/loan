<?php

namespace App\Service;

use Exception;

class LoanParameter
{
	//  In a real application these options would come from either the database or another component, for the purpose of this exercise repeating them helps to keep the scope limited
	private $creditOptions = ['poor', 'average', 'good', 'excellent'];
	
	/**
	 * returns the max loan amount for their credit rating
	 * 
	 * @throws Exception
	 */
	public function getMaxAmount(string $credit): float
	{
		if ( ! in_array($credit, $this->creditOptions)) {
			throw new Exception('invalid credit option');
		}
		
		//  Poor credit has a limit of $5000
		if ($credit == 'poor') {
			return 5000;
		}
		
		//  For everyone else, $75,000
		return 75000;
	}
	
	/**
	 * @throws Exception
	 */
	public function getInterestRate(int $term, string $credit): float
	{
		if ( ! in_array($credit, $this->creditOptions)) {
			throw new Exception('invalid credit option');
		}
		
		if ( $term < 12 || $term > 60) {
			throw new Exception('invalid term option');
		}
		
		if ($credit == 'average') {
			return 22.5;
		} elseif ($credit == 'good') {
			return 15.5;
		}elseif ($credit == 'excellent') {
			if( $term == 12 ){
				return 6.99;
			}elseif( $term <= 24 ){
				return 9.99;
			}elseif( $term == 36 ){
				return 12;
			}else{
				return 15;
			}
		}
		
		return 25;
	}
	
	/**
	 *
	 * Origination fee is 5% of the first $5000, and then 2% of the rest, rounded to the nearest dollar
	 *
	 * @param  int  $amount
	 *
	 * @return int
	 * @throws Exception
	 */
	public function getOriginationFee( int $amount): int
	{
		if( $amount < 1 ){
			throw new Exception( 'amount must be above zero');
		}
		
		if( $amount <= 5000 ){
			return round( $amount * 0.05 );
		}else{
			return round( 250 + ( ($amount - 5000) * 0.02) );
		}
	}
	
}
