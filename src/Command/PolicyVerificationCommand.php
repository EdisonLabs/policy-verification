<?php

namespace EdisonLabs\PolicyVerification\Command;

use EdisonLabs\PolicyVerification\Check\PolicyCheckInterface;
use EdisonLabs\PolicyVerification\Report;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Implements command for Policy verification.
 */
class PolicyVerificationCommand extends Command
{

  /**
   * @var array
   */
    protected $data = [];

    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    protected $io;

    /**
     * @var \EdisonLabs\PolicyVerification\Report
     */
    protected $report;

    /**
     * Sets a policy check to report.
     *
     * @param \EdisonLabs\PolicyVerification\Check\PolicyCheckInterface $policyCheck The policy check object.
     */
    public function setPolicyCheck(PolicyCheckInterface $policyCheck)
    {
        if (!$this->report) {
            return;
        }

        $this->report->setCheck($policyCheck);
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        // Gets data parameter.
        $data = $input->getOption('data');
        $data = $this->getDataArray($data);

        $this->data = $data;
        $this->report = new Report($this->data);

        // Checks and includes a specific policy check class in the report.
        $specificClass = $input->getOption('class');
        if ($specificClass) {
            if (!class_exists($specificClass)) {
                throw new RuntimeException(sprintf('Class %s does not exist', $specificClass));
            }

            $specificClass = new $specificClass();
            $this->setPolicyCheck($specificClass);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('edisonlabs:policy-verification')
            ->setDescription('Edison Labs Policy verification')
            ->setHelp('This command allows you to get results from policy checks')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'The output format: table, json', 'table')
            ->addOption('data', null, InputOption::VALUE_REQUIRED, 'Pass custom data to the policy checks, which can be a file or a string containing JSON format')
            ->addOption('class', null, InputOption::VALUE_REQUIRED, 'Specify a policy check class to be included in the list of checks to be reported')
        ;
    }

    /**
     * Converts and returns the data JSON parameter value to array.
     *
     * @param string $data The data command option.
     *
     * @return array The data array.
     */
    protected function getDataArray($data)
    {
        if (empty($data)) {
            return [];
        }

        // If parameter is a file.
        if (file_exists($data)) {
            $data = file_get_contents($data);
        }

        $data = trim($data);

        $data = json_decode($data, true);
        if (json_last_error() == JSON_ERROR_NONE) {
            return $data;
        }

        throw new RuntimeException('Data parameter must be a valid file or string with valid JSON format');
    }

    /**
     * Outputs the Policy summary report on table format.
     *
     * @param array                                             $policySummary The policy summary array. See Report->getResultSummary().
     * @param \Symfony\Component\Console\Output\OutputInterface $output        Console output object.
     *
     * @throws \Exception
     */
    protected function renderPolicySummaryAsTable($policySummary, OutputInterface $output)
    {
        $io = $this->io;

        // Prints result message.
        $score = $policySummary['score_compliant_percentage'];
        $totalPolicies = $policySummary['total_policies'];
        $totalCompliantPolicies = $policySummary['total_compliant_policies'];
        if ($policySummary['result'] == Report::REPORT_POLICY_NOT_COMPLIANT) {
            $io->block("Score $score% ($totalCompliantPolicies of $totalPolicies)", 'NOT COMPLIANT', 'fg=white;bg=red', ' ', true);
        } else {
            $io->block("Score 100% ($totalCompliantPolicies of $totalPolicies)", 'COMPLIANT', 'fg=white;bg=green', ' ', true);
        }

        // Prints non-compliant check results.
        $nonComplianceMessages = $this->report->getNonCompliantChecksResultMessages();
        if ($nonComplianceMessages) {
            $io->listing($nonComplianceMessages);
        }

        // Prints actions:
        $nonComplianceActions = $this->report->getNonCompliantChecksActions();
        if ($nonComplianceActions) {
            $io->text('Actions:');
            $io->listing($this->report->getNonCompliantChecksActions());
        }

        $table = new Table($output);
        $table->setStyle('box');
        $table->setHeaders([
            'Risk',
            'Result',
            'Name',
            'Category',
            'Result message',
        ]);

        $rows = [];

        foreach ($policySummary['policies'] as $policies) {
            foreach ($policies as $policy) {
                $rows[] = [
                    strtoupper($policy['risk']),
                    strtoupper($policy['result']),
                    $policy['name'],
                    $policy['category'],
                    $policy['message'],
                ];
            }
        }

        $table->setRows($rows);
        $table->render();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $policySummary = $this->report->getResultSummary();

        $format = $input->getOption('format');
        switch ($format) {
          case 'json':
            $output->writeln(json_encode($policySummary));
            break;

          case 'table':
            $this->renderPolicySummaryAsTable($policySummary, $output);
            break;

          default:
            throw new RuntimeException('Invalid format');
        }
    }
}
