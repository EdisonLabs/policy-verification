<?php
namespace EdisonLabs\PolicyVerification;

use EdisonLabs\PolicyVerification\Check\AbstractPolicyCheckBase;

/**
 * Implements class for Policies report.
 */
class Report
{

  const POLICY_VERIFICATION_NAMESPACE = 'EdisonLabs\PolicyVerification';
  const REPORT_POLICY_COMPLIANT = 'compliant';
  const REPORT_POLICY_NOT_COMPLIANT = 'not compliant';

  /**
   * Data passed in for checks and report.
   *
   * @var array
   */
  protected $data = [];

  /**
   * An array containing the policy check instances.
   *
   * @var array
   */
  protected $policyChecks = [];

  /**
   * Report constructor.
   *
   * @param array $data An array containing data to be passed in for checks and reports.
   */
  public function __construct(array $data = [])
  {
    $this->data = $data;
  }

  /**
   * Returns the data array.
   *
   * @return array The data.
   */
  public function getData()
  {
    return $this->data;
  }

  /**
   * Gets the policy check instances.
   *
   * @return array An array containing the policy check instances.
   *
   * @throws \Exception
   */
  public function getPolicyChecks()
  {
    if (empty($this->policyChecks)) {
      $containerBuilder = new ContainerBuilder($this->data);
      $containerBuilder = $containerBuilder->getContainerBuilder();

      $services = $containerBuilder->getServiceIds();

      foreach ($services as $serviceName) {
        // Filter by policy check classes only.
        if (strpos($serviceName, self::POLICY_VERIFICATION_NAMESPACE) === false) {
          continue;
        }

        /** @var \EdisonLabs\PolicyVerification\Check\PolicyCheckInterface $policyCheck */
        $policyCheck = $containerBuilder->get($serviceName);

        // Sanity check by class type.
        if (!$policyCheck instanceof AbstractPolicyCheckBase) {
          continue;
        }

        $this->policyChecks[] = $policyCheck;
      }
    }

    return $this->policyChecks;
  }

  /**
   * Gets the compliant policy check instances.
   *
   * @return array An array containing the compliant policy check instances.
   *
   * @throws \Exception
   */
  public function getPolicyChecksCompliant()
  {
    return array_filter($this->getPolicyChecks(), function ($policyCheck) {
      /** @var \EdisonLabs\PolicyVerification\Check\AbstractPolicyCheckBase $policyCheck */
      return $policyCheck->isCompliant();
    });
  }

  /**
   * Gets the not compliant policy check instances.
   *
   * @return array An array containing the not compliant policy check instances.
   *
   * @throws \Exception
   */
  public function getPolicyChecksNotCompliant()
  {
    return array_filter($this->getPolicyChecks(), function ($policyCheck) {
      /** @var \EdisonLabs\PolicyVerification\Check\AbstractPolicyCheckBase $policyCheck */
      return $policyCheck->isNotCompliant();
    });
  }

  /**
   * Returns the total number of existing checks.
   *
   * @return int The total number of checks.
   *
   * @throws \Exception
   */
  public function getTotalChecks()
  {
    return count($this->getPolicyChecks());
  }

  /**
   * Gets the report score.
   *
   * @return int The report score.
   *
   * @throws \Exception
   */
  public function getScore()
  {
    $totalChecks = $this->getTotalChecks();
    $totalNotCompliant = count($this->getPolicyChecksNotCompliant());

    return ($totalChecks - $totalNotCompliant);
  }

  /**
   * Gets the score percentage for compliant checks.
   *
   * @return int The score percentage.
   *
   * @throws \Exception
   */
  public function getCompliantScorePercentage()
  {
    $totalChecks = $this->getTotalChecks();
    $totalCompliantChecks = count($this->getPolicyChecksCompliant());

    return round((100 * $totalCompliantChecks) / $totalChecks);
  }

  /**
   * Gets the report result.
   *
   * @return string Compliant, not compliant.
   *
   * @throws \Exception
   */
  public function getResult()
  {
    if (count($this->getPolicyChecksNotCompliant()) > 0) {
      return self::REPORT_POLICY_NOT_COMPLIANT;
    }

    return self::REPORT_POLICY_COMPLIANT;
  }

  /**
   * Returns an array containing the report summary.
   *
   * @return array The report summary.
   *
   * @throws \Exception
   */
  public function getResultSummary()
  {
    $summary = [
      'data' => $this->data,
      'timestamp' => time(),
      'result' => $this->getResult(),
      'total_policies' => $this->getTotalChecks(),
      'total_compliant_policies' => count($this->getPolicyChecksCompliant()),
      'total_not_compliant_policies' => count($this->getPolicyChecksNotCompliant()),
      'score_compliant_percentage' => $this->getCompliantScorePercentage(),
      'policies' => [],
    ];

    /** @var \EdisonLabs\PolicyVerification\Check\AbstractPolicyCheckBase $policyCheck */
    foreach ($this->getPolicyChecks() as $policyCheck) {
      $summary['policies'][$policyCheck->getCategory()][$policyCheck->getName()] = [
        'name' => $policyCheck->getName(),
        'description' => $policyCheck->getDescription(),
        'category' => $policyCheck->getCategory(),
        'result' => $policyCheck->isCompliant() ? self::REPORT_POLICY_COMPLIANT : self::REPORT_POLICY_NOT_COMPLIANT,
        'message' => $policyCheck->getResultMessage(),
        'actions' => $policyCheck->isNotCompliant() ? $policyCheck->getActions() : [],
        'risk' => $policyCheck->getRiskLevel(),
      ];
    }

    return $summary;
  }

  /**
   * Returns a JSON string containing the policy check results.
   *
   * @return string The results on JSON format.
   *
   * @throws \Exception
   */
  public function jsonExport()
  {
    return json_encode($this->getResultSummary());
  }

}
