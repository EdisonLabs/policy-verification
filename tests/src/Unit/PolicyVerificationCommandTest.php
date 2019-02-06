<?php

namespace EdisonLabs\PolicyVerification\Unit;

use EdisonLabs\PolicyVerification\Check\AbstractPolicyCheckBase;
use EdisonLabs\PolicyVerification\Command\PolicyVerificationCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Tests \EdisonLabs\PolicyVerification\Command\PolicyVerificationCommand.
 *
 * @group policy-verification
 */
class PolicyVerificationCommandTest extends TestCase
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

        require __DIR__.'/../../ExamplePolicyCheck.php';
    }

    /**
     * Tests the PolicyVerificationCommand.
     */
    public function testPolicyVerificationCommand()
    {
        $command = new PolicyVerificationCommand();

        // Test command options.
        $this->assertEquals('edisonlabs:policy-verification', $command->getName());
        $this->assertEquals('Edison Labs Policy verification', $command->getDescription());
        $this->assertTrue($command->getDefinition()->hasOption('format'));
        $this->assertTrue($command->getDefinition()->hasOption('data'));
        $this->assertTrue($command->getDefinition()->hasOption('class'));

        $tester = new CommandTester($command);

        $tester->execute([
            '--class' => '\EdisonLabs\PolicyVerification\Test\ExamplePolicyCheck',
        ]);
        $output = $tester->getDisplay();

        // Test result output.
        $this->assertContains('[NOT COMPLIANT] Score 0% (0 of 1)', $output);
        $this->assertContains('The policy is not compliant', $output);
        $this->assertContains('Just an action example', $output);
        $this->assertContains('Example policy check', $output);

        // Test JSON output.
        $tester->execute([
            '--format' => 'json',
            '--class' => '\EdisonLabs\PolicyVerification\Test\ExamplePolicyCheck',
            '--data' => '{"mydata": "value"}'
        ]);
        $this->assertEquals(0, $tester->getStatusCode());
        $output = $tester->getDisplay();

        // Unset timestamp.
        $output = json_decode($output, true);
        unset($output['timestamp']);
        $output = json_encode($output);

        $this->assertContains('{"data":[],"result":"not compliant","total_policies":1,"total_compliant_policies":0,"total_non_compliant_policies":1,"score_compliant_percentage":0,"policies":{"Test":{"Example policy check":{"name":"Example policy check","description":"This is an example of a policy check","category":"Test","result":"not compliant","message":"The policy is not compliant","actions":["Just an action example."],"risk":"high"}}}}', $output);
    }
}
