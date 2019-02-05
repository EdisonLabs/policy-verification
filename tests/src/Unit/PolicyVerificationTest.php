<?php

namespace EdisonLabs\PolicyVerification\Unit;

use EdisonLabs\PolicyVerification\Check\AbstractPolicyCheckBase;
use EdisonLabs\PolicyVerification\ContainerBuilder;
use EdisonLabs\PolicyVerification\Report;
use PHPUnit\Framework\TestCase;

/**
 * Tests generation of policy-verification.
 *
 * @group policy-verification
 */
class PolicyVerificationTest extends TestCase
{

  /**
   * {@inheritdoc}
   */
    protected function setUp()
    {
        if (!defined('COMPOSER_INSTALL')) {
            $root = __DIR__.'/../../';
            $sources = [
                $root.'/../../autoload.php',
                $root.'/../vendor/autoload.php',
                $root.'/vendor/autoload.php',
            ];
            foreach ($sources as $file) {
                if (file_exists($file)) {
                    define('COMPOSER_INSTALL', $file);
                    break;
                }
            }
        }
    }

    /**
     * Basic test to get success result.
     */
    public function testPolicyVerification()
    {
        $this->assertTrue(true);
    }

    /**
     * Covers \EdisonLabs\PolicyVerification\ContainerBuilder
     */
    public function testContainerBuilder()
    {
        $containerBuilder = new ContainerBuilder();
        $symfonyContainerBuilder = $containerBuilder->getContainerBuilder();
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerBuilder', $symfonyContainerBuilder);
    }

    /**
     * Covers \EdisonLabs\PolicyVerification\Report
     */
    public function testReport()
    {
        $report = new Report([]);
        $check = $this->getMockBuilder('EdisonLabs\PolicyVerification\Check\AbstractPolicyCheckBase')
          ->setConstructorArgs([])
          ->getMockForAbstractClass();
        $report->setCheck($check);
        $checks = $report->getChecks();
        $this->assertNotEmpty($checks);
        $this->assertTrue(is_array($checks));
    }

    /**
     * Covers \EdisonLabs\PolicyVerification\Check\AbstractPolicyCheckBase
     *
     * @return null
     */
    public function testAbstractPolicyCheckBase()
    {
        /** @var \EdisonLabs\PolicyVerification\Check\AbstractPolicyCheckBase $checkBaseMock */
        $checkBaseMock = $this->getMockBuilder('EdisonLabs\PolicyVerification\Check\AbstractPolicyCheckBase')
        ->setMethods([
            'getName',
            'getDescription',
            'getCategory',
            'getRiskLevel',
            'getResultCompliantMessage',
            'getResultNotCompliantMessage',
            'getActions',
        ])
        ->setConstructorArgs([['mytestdata' => 'ok']])
        ->getMockForAbstractClass();

        $checkBaseMock->expects($this->once())
        ->method('check')
        ->willReturn(AbstractPolicyCheckBase::POLICY_COMPLIANT);

        $checkBaseMock->expects($this->once())
        ->method('getName')
        ->willReturn('Test policy Check');

        $checkBaseMock->expects($this->once())
        ->method('getDescription')
        ->willReturn('Test policy Check description');

        $checkBaseMock->expects($this->once())
        ->method('getCategory')
        ->willReturn('Test');

        $checkBaseMock->expects($this->once())
        ->method('getRiskLevel')
        ->willReturn(AbstractPolicyCheckBase::POLICY_RISK_LOW);

        $checkBaseMock->expects($this->any())
        ->method('getResultCompliantMessage')
        ->willReturn('This policy is compliant');

        $checkBaseMock->expects($this->once())
        ->method('getResultNotCompliantMessage')
        ->willReturn('This policy is not compliant');

        $checkBaseMock->expects($this->once())
        ->method('getActions')
        ->willReturn([
            'First action for test',
            'Second action for test',
        ]);

        $data = $checkBaseMock->getData();
        $this->assertArrayHasKey('mytestdata', $data);
        $this->assertEquals($data['mytestdata'], 'ok');
        $this->assertEquals('Test policy Check', $checkBaseMock->getName());
        $this->assertEquals('Test policy Check description', $checkBaseMock->getDescription());
        $this->assertEquals('Test', $checkBaseMock->getCategory());
        $this->assertEquals(AbstractPolicyCheckBase::POLICY_RISK_LOW, $checkBaseMock->getRiskLevel());
        $this->assertEquals('This policy is compliant', $checkBaseMock->getResultCompliantMessage());
        $this->assertEquals('This policy is not compliant', $checkBaseMock->getResultNotCompliantMessage());
        $this->assertEquals(['First action for test', 'Second action for test'], $checkBaseMock->getActions());
        $this->assertEquals(AbstractPolicyCheckBase::POLICY_COMPLIANT, $checkBaseMock->getResult());
        $this->assertEquals('This policy is compliant', $checkBaseMock->getResultMessage());
        $this->assertTrue($checkBaseMock->isCompliant());
        $this->assertFalse($checkBaseMock->isNotCompliant());
    }
}
