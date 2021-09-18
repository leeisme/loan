<?php

namespace App\Tests\Service;

use Exception;
use PHPUnit\Framework\AssertionFailedError;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Service\LoanParameter;

class LoanParameterTest extends KernelTestCase
{
	public function testMaxAmount(): void
	{
		$kernel = self::bootKernel();
		$container = static::getContainer();
		
		/** @var LoanParameter $loanParameterService */
		$loanParameterService = $container->get(LoanParameter::class);
		
		$this->assertEquals(5000, $loanParameterService->getMaxAmount('poor'));
		
		$this->assertEquals(75000, $loanParameterService->getMaxAmount('average'));
		$this->assertEquals(75000, $loanParameterService->getMaxAmount('good'));
		$this->assertEquals(75000, $loanParameterService->getMaxAmount('excellent'));
		
		try {
			$loanParameterService->getMaxAmount('invalid Option');
			$this->fail('Should have thrown an exception for invalid credit option');
		} catch (AssertionFailedError $e) {
			//  Pass Through failure
			throw $e;
		} catch (Exception $e) {
			$this->assertEquals('invalid credit option', $e->getMessage());
		}
	}
	
	public function testInterestRates()
	{
		$kernel = self::bootKernel();
		$container = static::getContainer();
		
		/** @var LoanParameter $loanParameterService */
		$loanParameterService = $container->get(LoanParameter::class);
		
		
		$this->assertEquals(25, $loanParameterService->getInterestRate(12, 'poor'));
		
		$this->assertEquals(25, $loanParameterService->getInterestRate(48, 'poor'));
		
		$this->assertEquals(22.5, $loanParameterService->getInterestRate(12, 'average'));
		$this->assertEquals(22.5, $loanParameterService->getInterestRate(48, 'average'));
		
		$this->assertEquals(15.5, $loanParameterService->getInterestRate(12, 'good'));
		$this->assertEquals(15.5, $loanParameterService->getInterestRate(48, 'good'));
		
		$this->assertEquals(6.99, $loanParameterService->getInterestRate(12, 'excellent'));
		$this->assertEquals(9.99, $loanParameterService->getInterestRate(18, 'excellent'));
		$this->assertEquals(12, $loanParameterService->getInterestRate(36, 'excellent'));
		$this->assertEquals(15, $loanParameterService->getInterestRate(48, 'excellent'));
		$this->assertEquals(15, $loanParameterService->getInterestRate(60, 'excellent'));
		
		try {
			$loanParameterService->getInterestRate(12, 'invalid Option');
			$this->fail('Should have thrown an exception for invalid credit option');
		} catch (AssertionFailedError $e) {
			//  Pass Through failure
			throw $e;
		} catch (Exception $e) {
			$this->assertEquals('invalid credit option', $e->getMessage());
		}
		
		try {
			$loanParameterService->getInterestRate(5, 'excellent');
			$this->fail('Should have thrown an exception for invalid term option');
		} catch (AssertionFailedError $e) {
			//  Pass Through failure
			throw $e;
		} catch (Exception $e) {
			$this->assertEquals('invalid term option', $e->getMessage());
		}
		
		try {
			$loanParameterService->getInterestRate(65, 'excellent');
			$this->fail('Should have thrown an exception for invalid term option');
		} catch (AssertionFailedError $e) {
			//  Pass Through failure
			throw $e;
		} catch (Exception $e) {
			$this->assertEquals('invalid term option', $e->getMessage());
		}
	}
	
	
	public function testOriginationFee()
	{
		$kernel = self::bootKernel();
		$container = static::getContainer();
		
		/** @var LoanParameter $loanParameterService */
		$loanParameterService = $container->get(LoanParameter::class);
		
		$this->assertEquals(50, $loanParameterService->getOriginationFee(1000));
		$this->assertEquals(250, $loanParameterService->getOriginationFee(5000));
		$this->assertEquals(270, $loanParameterService->getOriginationFee(6000));
		
		try {
			$loanParameterService->getOriginationFee(-5000);
			$this->fail('Should have thrown an exception for invalid amount');
		} catch (AssertionFailedError $e) {
			//  Pass Through failure
			throw $e;
		} catch (Exception $e) {
			$this->assertEquals('amount must be above zero', $e->getMessage());
		}
		
		
		
	}
}
