<?php

namespace App\Tests\Service;

use PHPUnit\Framework\AssertionFailedError;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Service\LoanCalculator;
use Exception;

class LoanCalculatorTest extends KernelTestCase
{
	/**
	 * @throws Exception
	 */
	public function testMonthlyPayment(): void
    {
	    $kernel = self::bootKernel();
	    $container = static::getContainer();
	
	    /** @var LoanCalculator $loanCalculatorService */
	    $loanCalculatorService = $container->get(LoanCalculator::class);
    	
	    $this->assertEquals( 100, $loanCalculatorService->getMonthlyPayment( 1000, 0, 10 ) );
		$this->assertEquals( 13.22, $loanCalculatorService->getMonthlyPayment( 1000, 10, 120 ) );
	    
	    $this->assertEquals( 1000, $loanCalculatorService->getMonthlyPayment( 1000, 10, 0 ) );
	    $this->assertEquals( 1000, $loanCalculatorService->getMonthlyPayment( 1000, 0, 0 ) );
		
	    $this->assertEquals( 212.47, $loanCalculatorService->getMonthlyPayment( 10000, 10, 60 ) );
		
	    try {
		    $loanCalculatorService->getMonthlyPayment(-5000, 5, 10 );
		    $this->fail('Should have thrown an exception for invalid principal amount');
	    } catch (AssertionFailedError $e) {
		    //  Pass Through failure
		    throw $e;
	    } catch (Exception $e) {
		    $this->assertEquals('invalid principal amount', $e->getMessage());
	    }
	
	    try {
		    $loanCalculatorService->getMonthlyPayment(5000, -5, 10 );
		    $this->fail('Should have thrown an exception for invalid Interest rate');
	    } catch (AssertionFailedError $e) {
		    //  Pass Through failure
		    throw $e;
	    } catch (Exception $e) {
		    $this->assertEquals('invalid Interest rate', $e->getMessage());
	    }
	
	    try {
		    $loanCalculatorService->getMonthlyPayment(5000, 5, -10 );
		    $this->fail('Should have thrown an exception for invalid term');
	    } catch (AssertionFailedError $e) {
		    //  Pass Through failure
		    throw $e;
	    } catch (Exception $e) {
		    $this->assertEquals('invalid term', $e->getMessage());
	    }
	    
    }
	
	/**
	 * DataProvider for testPaymentSchedule
	 * @return int[][]
	 */
	function dataPaymentSchedule(): array
	{
		return [
			[1000,  10, 12, false],
			[2000,  10, 12, false],
			[3000,  10, 12, false],
			[4000,  10, 12, false],
			[5000,  10, 12, false],
			[1000,  15, 12, false],
			[1000,  20, 12, false],
			[1000,  25, 12, false],
			[1000,  30, 12, false],
			[1000,  10, 18, false],
			[1000,  10, 24, false],
			[1000,  10, 36, false],
			[1000,  10, 60, false],
			[1000,  10,  1, false],
			// Fails simple validation
			[1000,  10,  0,  true],
			[0,     10, 12,  true],
			// 0% APR does not throw and is handled
			[1000,   0, 12, false],
		];
	}
	
	/**
	 * @dataProvider dataPaymentSchedule
	 * @throws Exception
	 */
	function testPaymentSchedule(
		float $loanAmount,
		float $interestRate,
		int $termInMonths,
		bool $expectException
	): void 
	{
		$kernel = self::bootKernel();
		$container = static::getContainer();
		
		/** @var LoanCalculator $loanCalculatorService */
		$loanCalculatorService = $container->get(LoanCalculator::class);
		
		$this->assertTrue(true);
		
		// Expect an exception?
		if ($expectException) {
			$this->expectException(RuntimeException::class);
		}
		
		// Get resulting payment schedule and test its values.
		$schedule = $loanCalculatorService->getPaymentSchedule(
			$loanAmount,
			$interestRate,
			$termInMonths
		);
		
		// Calculated entire term?
		$this->assertCount($termInMonths, $schedule);
		
		// Balance is zero at end of term
		$lastRecord = end($schedule);
		$this->assertIsNumeric($lastRecord['balance']);
		$this->assertEquals(0, $lastRecord['balance']);
		
		// Compare payment calculated by this function with that calculated by
		// $this->getMonthlyPayment()
		$existingGetPaymentResult = $loanCalculatorService->getMonthlyPayment(
			$loanAmount,
			$interestRate,
			$termInMonths
		);
		
		$this->assertEquals($lastRecord['payment'], $existingGetPaymentResult);
	}
}
