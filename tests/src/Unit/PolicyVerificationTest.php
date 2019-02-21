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
        $data = ['mydata' => 'value'];
        $report = new Report($data);
        $check = $this->getMockBuilder('EdisonLabs\PolicyVerification\Check\AbstractPolicyCheckBase')
          ->getMockForAbstractClass();
        $report->setCheck($check);
        $checks = $report->getChecks();
        $this->assertNotEmpty($checks);
        $this->assertTrue(is_array($checks));
        $this->assertEquals($data, $report->getData());
        $this->assertEquals(0, $report->getScore());
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
            'getSeverity',
            'getResultPassMessage',
            'getResultFailMessage',
            'getWarningMessage',
            'getActions',
        ])
        ->getMockForAbstractClass();

        $checkBaseMock->setData(['mytestdata' => 'ok']);

        $checkBaseMock->expects($this->once())
        ->method('check')
        ->willReturn(AbstractPolicyCheckBase::POLICY_PASS);

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
        ->method('getSeverity')
        ->willReturn(AbstractPolicyCheckBase::POLICY_SEVERITY_LOW);

        $checkBaseMock->expects($this->any())
        ->method('getResultPassMessage')
        ->willReturn('This policy passes');

        $checkBaseMock->expects($this->once())
        ->method('getResultFailMessage')
        ->willReturn('This policy fails');

        $checkBaseMock->expects($this->once())
        ->method('getWarningMessage')
        ->willReturn('Just an example of warning message');

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
        $this->assertEquals(AbstractPolicyCheckBase::POLICY_SEVERITY_LOW, $checkBaseMock->getSeverity());
        $this->assertEquals('This policy passes', $checkBaseMock->getResultPassMessage());
        $this->assertEquals('This policy fails', $checkBaseMock->getResultFailMessage());
        $this->assertEquals('Just an example of warning message', $checkBaseMock->getWarningMessage());
        $this->assertEquals(['First action for test', 'Second action for test'], $checkBaseMock->getActions());
        $this->assertEquals(AbstractPolicyCheckBase::POLICY_PASS, $checkBaseMock->getResult());
        $this->assertEquals('This policy passes', $checkBaseMock->getResultMessage());
        $this->assertTrue($checkBaseMock->isPass());
        $this->assertFalse($checkBaseMock->isFail());
    }
}
