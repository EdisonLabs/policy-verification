<?php

namespace EdisonLabs\PolicyVerification\Unit;

use EdisonLabs\PolicyVerification\Command\PolicyVerificationCommand;
use PHPUnit\Framework\TestCase;
use RuntimeException;
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

        require_once __DIR__.'/../../ExamplePolicyCheck.php';
        require_once __DIR__.'/../../ExamplePolicyCheckRequirementError.php';
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

        // Test fail result output.
        $tester->execute([
            '--class' => '\EdisonLabs\PolicyVerification\Test\ExamplePolicyCheck',
        ]);
        $output = $tester->getDisplay();
        $this->assertContains('[FAIL] Score 0% (0 of 1)', $output);
        $this->assertContains('[FAIL] (HIGH) Example policy check: The policy fails', $output);
        $this->assertContains('* Example policy check: Just an action example.', $output);
        $this->assertContains('* Example policy check: Just an example of warning message', $output);

        // Test fail JSON output.
        $tester->execute([
            '--format' => 'json',
            '--class' => '\EdisonLabs\PolicyVerification\Test\ExamplePolicyCheck',
        ]);
        $this->assertEquals(0, $tester->getStatusCode());
        $output = $tester->getDisplay();

        // Unset timestamp.
        $output = json_decode($output, true);
        unset($output['timestamp']);
        $output = json_encode($output);

        $this->assertContains('{"data":[],"result":false,"summary":{"total":1,"total_pass":0,"total_fail":1,"percentage_pass":0},"policies":{"test":{"example_policy_check":{"name":"Example policy check","description":"This is an example of a policy check","category":"Test","requirement_errors":[],"result":false,"message":"The policy fails","warnings":["Just an example of warning message"],"actions":["Just an action example."],"severity":"high"}}},"messages":{"pass":[],"fail":["The policy fails"],"action":["Just an action example."],"warning":["Just an example of warning message"],"requirement_error":[]}}', $output);

        // Test pass result output.
        $tester->execute([
            '--class' => '\EdisonLabs\PolicyVerification\Test\ExamplePolicyCheck',
            '--data' => __DIR__.'/../../data.json',
        ]);
        $output = $tester->getDisplay();

        $this->assertContains('[PASS] Score 100% (1 of 1)', $output);
        $this->assertContains('* Example policy check: Just an example of warning message', $output);
        $this->assertContains('[PASS] (HIGH) Example policy check: The policy passes', $output);
        $this->assertNotContains('Requirement errors', $output);

        // Test pass JSON output.
        $tester->execute([
            '--format' => 'json',
            '--data-exclude-output' => '""',
            '--class' => '\EdisonLabs\PolicyVerification\Test\ExamplePolicyCheck',
            '--data' => __DIR__.'/../../data.json',
        ]);
        $this->assertEquals(0, $tester->getStatusCode());
        $output = $tester->getDisplay();

        // Unset timestamp.
        $output = json_decode($output, true);
        unset($output['timestamp']);
        $output = json_encode($output);

        $this->assertContains('{"data":{"pass":1},"result":true,"summary":{"total":1,"total_pass":1,"total_fail":0,"percentage_pass":100},"policies":{"test":{"example_policy_check":{"name":"Example policy check","description":"This is an example of a policy check","category":"Test","requirement_errors":[],"result":true,"message":"The policy passes","warnings":["Just an example of warning message"],"actions":["Just an action example."],"severity":"high"}}},"messages":{"pass":["The policy passes"],"fail":[],"action":["Just an action example."],"warning":["Just an example of warning message"],"requirement_error":[]}}', $output);

        // Test exceptions.
        $this->expectException(RuntimeException::class);
        $tester->execute([
            '--class' => '\NotAClass',
        ]);
        $tester->execute([
            '--data' => 'not valid data format, must be JSON',
        ]);
        $tester->execute([
            '--format' => 'Invalid format',
        ]);
    }

    /**
     * Tests the PolicyVerificationCommand with Check requirement error.
     */
    public function testPolicyVerificationCommandForCheckRequirementError()
    {
        $command = new PolicyVerificationCommand();
        $tester = new CommandTester($command);

        $tester->execute([
            '--class' => '\EdisonLabs\PolicyVerification\Test\ExamplePolicyCheckRequirementError',
        ]);
        $output = $tester->getDisplay();

        $this->assertContains('[FAIL] Score 0% (0 of 1)', $output);
        $this->assertContains('[FAIL] (HIGH) Example policy check: Could not proceed with policy verification due to requirement errors', $output);
        $this->assertNotContains('* Example policy check: Just an action example.', $output);
        $this->assertNotContains('* Example policy check: Just an example of warning message', $output);
        $this->assertContains('Requirement errors', $output);
        $this->assertContains('Invalid data', $output);
    }
}
