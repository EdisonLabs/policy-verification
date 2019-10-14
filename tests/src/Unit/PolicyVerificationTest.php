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
     * Returns a mocked Policy check class instance.
     *
     * @return \EdisonLabs\PolicyVerification\Check\AbstractPolicyCheckBase The policy check instance.
     */
    public function getCheckBaseMock()
    {
        /** @var \EdisonLabs\PolicyVerification\Check\AbstractPolicyCheckBase $checkBaseMock */
        $checkBaseMock = $this->getMockBuilder('EdisonLabs\PolicyVerification\Check\AbstractPolicyCheckBase')
        ->setMethods([
            'skipCheck',
            'getName',
            'getDescription',
            'getCategory',
            'getSeverity',
            'getResultPassMessage',
            'getResultFailMessage',
        ])
        ->getMockForAbstractClass();

        $warnings = ['Just an example of warning message'];
        $actions = [
            'First action for test',
            'Second action for test',
        ];

        $checkBaseMock->setData(['mytestdata' => 'ok']);
        $checkBaseMock->setWarnings($warnings);
        $checkBaseMock->setActions($actions);
        $checkBaseMock->setResultPassMessage('This policy passes');
        $checkBaseMock->setResultFailMessage('This policy fails');

        $checkBaseMock->expects($this->any())
        ->method('check')
        ->willReturn(AbstractPolicyCheckBase::POLICY_PASS);

        $checkBaseMock->expects($this->any())
        ->method('skipCheck')
        ->willReturn(false);

        $checkBaseMock->expects($this->any())
        ->method('getName')
        ->willReturn('Test policy Check');

        $checkBaseMock->expects($this->any())
        ->method('getDescription')
        ->willReturn('Test policy Check description');

        $checkBaseMock->expects($this->any())
        ->method('getCategory')
        ->willReturn('Test');

        $checkBaseMock->expects($this->any())
        ->method('getSeverity')
        ->willReturn(AbstractPolicyCheckBase::POLICY_SEVERITY_LOW);

        $checkBaseMock->expects($this->any())
        ->method('getResultPassMessage')
        ->willReturn('This policy passes');

        $checkBaseMock->expects($this->any())
        ->method('getResultFailMessage')
        ->willReturn('This policy fails');

        return $checkBaseMock;
    }

    /**
     * Covers \EdisonLabs\PolicyVerification\Check\AbstractPolicyCheckBase
     *
     * @return null
     */
    public function testAbstractPolicyCheckBase()
    {
        $checkBaseMock = $this->getCheckBaseMock();

        $warnings = ['Just an example of warning message'];
        $actions = [
            'First action for test',
            'Second action for test',
        ];

        $data = $checkBaseMock->getData();
        $this->assertArrayHasKey('mytestdata', $data);
        $this->assertEquals($data['mytestdata'], 'ok');
        $this->assertEquals(false, $checkBaseMock->skipCheck());
        $this->assertEquals('Test policy Check', $checkBaseMock->getName());
        $this->assertEquals('Test policy Check description', $checkBaseMock->getDescription());
        $this->assertEquals('Test', $checkBaseMock->getCategory());
        $this->assertEquals(AbstractPolicyCheckBase::POLICY_SEVERITY_LOW, $checkBaseMock->getSeverity());
        $this->assertEquals('This policy passes', $checkBaseMock->getResultPassMessage());
        $this->assertEquals('This policy fails', $checkBaseMock->getResultFailMessage());
        $this->assertEquals($warnings, $checkBaseMock->getWarnings());
        $this->assertEquals($actions, $checkBaseMock->getActions());
        $this->assertEquals(AbstractPolicyCheckBase::POLICY_PASS, $checkBaseMock->getResult());
        $this->assertEquals('This policy passes', $checkBaseMock->getResultMessage());
        $this->assertTrue($checkBaseMock->isPass());
        $this->assertFalse($checkBaseMock->isFail());
    }

    /**
     * Test a policy check with requirement error.
     *
     * @return null
     */
    public function testAbstractPolicyCheckBaseRequirementError()
    {
        /** @var \EdisonLabs\PolicyVerification\Check\AbstractPolicyCheckBase $checkBaseMock */
        $checkBaseMock = $this->getCheckBaseMock();

        $checkBaseMock->checkRequirements();
        $checkBaseMock->setRequirementErrors(['Some data is missing']);

        $this->assertEquals(AbstractPolicyCheckBase::POLICY_FAIL, $checkBaseMock->getResult());
        $this->assertEquals('Could not proceed with policy verification due to requirement errors', $checkBaseMock->getResultMessage());
        $this->assertEquals(['Some data is missing'], $checkBaseMock->getRequirementErrors());
    }
}
