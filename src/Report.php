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
    public function getChecks()
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
    public function getCompliantChecks()
    {
        return array_filter($this->getChecks(), function ($policyCheck) {

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
    public function getNonCompliantChecks()
    {
        return array_filter($this->getChecks(), function ($policyCheck) {

            return $policyCheck->isNotCompliant();
        });
    }

    /**
     * Returns an array containing result messages of non-compliant checks.
     *
     * @return array
     *   An array of messages.
     *
     * @throws \Exception
     */
    public function getNonCompliantChecksResultMessages()
    {
        $messages = [];

        /** @var \EdisonLabs\PolicyVerification\Check\AbstractPolicyCheckBase $policyCheck */
        foreach ($this->getNonCompliantChecks() as $policyCheck) {
            $messages[] = $policyCheck->getResultMessage();
        }

        return $messages;
    }

    /**
     * Returns an array containing action messages of non-compliant checks.
     *
     * @return array
     *   An array of action messages.
     *
     * @throws \Exception
     */
    public function getNonCompliantChecksActions()
    {
        $messages = [];

        /** @var \EdisonLabs\PolicyVerification\Check\AbstractPolicyCheckBase $policyCheck */
        foreach ($this->getNonCompliantChecks() as $policyCheck) {
            $actions = $policyCheck->getActions();

            foreach ($actions as $action) {
                $messages[] = $action;
            }
        }

        return $messages;
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
        return count($this->getChecks());
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
        $totalNonCompliant = count($this->getNonCompliantChecks());

        return ($totalChecks - $totalNonCompliant);
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
        $totalCompliantChecks = count($this->getCompliantChecks());

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
        if (count($this->getNonCompliantChecks()) > 0) {
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
            'total_compliant_policies' => count($this->getCompliantChecks()),
            'total_non_compliant_policies' => count($this->getNonCompliantChecks()),
            'score_compliant_percentage' => $this->getCompliantScorePercentage(),
            'policies' => [],
        ];

        // Makes non-compliant checks be listed first.
        $checks = $this->getNonCompliantChecks() + $this->getCompliantChecks();

        /** @var \EdisonLabs\PolicyVerification\Check\AbstractPolicyCheckBase $policyCheck */
        foreach ($checks as $policyCheck) {
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
}
