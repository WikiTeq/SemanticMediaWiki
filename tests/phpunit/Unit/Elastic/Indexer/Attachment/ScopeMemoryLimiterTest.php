<?php

namespace SMW\Tests\Unit\Elastic\Indexer\Attachment;

use SMW\Elastic\Indexer\Attachment\ScopeMemoryLimiter;

/**
 * @covers \SMW\Elastic\Indexer\Attachment\ScopeMemoryLimiter
 * @group semantic-mediawiki
 *
 * @license GNU GPL v2+
 * @since 3.2
 *
 * @author mwjames
 */
class ScopeMemoryLimiterTest extends \PHPUnit_Framework_TestCase {

	private $testCaller;
	private $memoryLimitFromCallable;

	public function testCanConstruct() {

		$this->assertInstanceOf(
			ScopeMemoryLimiter::class,
			new ScopeMemoryLimiter()
		);
	}

	public function testExecute() {

		$this->testCaller = $this->getMockBuilder( '\stdClass' )
			->disableOriginalConstructor()
			->setMethods( [ 'calledFromCallable' ] )
			->getMock();

		$this->testCaller->expects( $this->once() )
			->method( 'calledFromCallable' );

		$memoryLimitBefore = ini_get( 'memory_limit' );
		$memoryLimit = '1M';

		// Establish that the limits are different
		$this->assertNotEquals(
			$memoryLimitBefore,
			$memoryLimit
		);

		$instance = new ScopeMemoryLimiter(
			$memoryLimit
		);

		$instance->execute( [ $this, 'runCallable' ] );

		// Establish that the limit is equal to what we have expected
		// to be set
		$this->assertEquals(
			$memoryLimit,
			$this->memoryLimitFromCallable
		);

		// Establish that the initial limit has been reset
		$this->assertEquals(
			$memoryLimitBefore,
			$instance->getMemoryLimit()
		);
	}

	public function runCallable() {
		$this->memoryLimitFromCallable = ini_get( 'memory_limit' );
		$this->testCaller->calledFromCallable();
	}

	/**
	 * @dataProvider toIntProvider
	 */
	public function testToInt( $string, $expected ) {

		$instance = new ScopeMemoryLimiter();

		$this->assertEquals(
			$expected,
			$instance->toInt( $string )
		);
	}

	public static function toIntProvider() {

		yield 'Empty string' => [
			'',
			-1,
		];

		yield 'String of spaces' => [
			'     ',
			-1,
		];

		yield 'One kb uppercased' => [
			'1K',
			1024
		];

		yield 'One kb lowercased' => [
			'1k',
			1024
		];

		yield 'One meg uppercased' => [
			'1M',
			1024 * 1024
		];

		yield 'One meg lowercased' => [
			'1m',
			1024 * 1024
		];

		yield 'One gig uppercased' => [
			'1G',
			1024 * 1024 * 1024
		];

		yield 'One gig lowercased' => [
			'1g',
			1024 * 1024 * 1024
		];
	}

}
