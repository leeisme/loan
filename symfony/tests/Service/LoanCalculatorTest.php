<?php

namespace App\Tests\Service;

use PHPUnit\Framework\AssertionFailedError;
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
}
