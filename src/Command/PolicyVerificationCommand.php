<?php

namespace EdisonLabs\PolicyVerification\Command;

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
   * {@inheritdoc}
   */
  protected function initialize(InputInterface $input, OutputInterface $output)
  {
    $this->io = new SymfonyStyle($input, $output);

    // Gets data parameter.
    $data = $input->getOption('data');
    $data = $this->getDataArray($data);

    $this->data = $data;
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
    ;
  }

  /**
   * Converts and returns the data JSON parameter value to array.
   *
   * @param string $data The data JSON value.
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

    throw new RuntimeException('Data parameter must be a valid JSON format');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {

    $report = new Report($this->data);

    $checkResults = $report->getResultSummary();
    $checkResults = $report->jsonExport();

    echo "<pre>" . print_r($checkResults, TRUE) . "</pre>"; die;
  }
}