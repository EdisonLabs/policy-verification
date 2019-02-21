<?php
namespace EdisonLabs\PolicyVerification;

use EdisonLabs\PolicyVerification\Check\PolicyCheckInterface;
use RuntimeException;

/**
 * Implements class for Policies report.
 */
class Report
{
    const POLICY_VERIFICATION_NAMESPACE = 'EdisonLabs\PolicyVerification';

    /**
     * Data passed in for checks.
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
   * A flag indicating whether the policy checks were located or not.
   *
   * @var bool
   */
    protected $policyChecksLocated = false;

    /**
     * Report constructor.
     *
     * @param array $data An array containing data to be passed in for checks.
     */
    public function __construct(array $data = [])
    {
        $this->setData($data);
    }

    /**
     * Sets the data array.
     *
     * @param array The data.
     */
    public function setData(array $data = [])
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
     * Sets a policy check.
     *
     * @param \EdisonLabs\PolicyVerification\Check\PolicyCheckInterface $policyCheck The policy check object.
     */
    public function setCheck(PolicyCheckInterface $policyCheck)
    {
        $policyCheck->setData($this->getData());

        $this->policyChecks[] = $policyCheck;
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
        if (!$this->policyChecksLocated) {
            $containerBuilder = new ContainerBuilder();
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
                if (!$policyCheck instanceof PolicyCheckInterface) {
                    throw new RuntimeException(sprintf('The policy check class %s must be an instance of EdisonLabs\PolicyVerification\Check\PolicyCheckInterface', $serviceName));
                }

                $this->setCheck($policyCheck);
            }

            $this->policyChecksLocated = true;
        }

        return $this->policyChecks;
    }

    /**
     * Gets the pass policy check instances.
     *
     * @return array An array containing the pass policy check instances.
     *
     * @throws \Exception
     */
    public function getPassChecks()
    {
        return array_filter($this->getChecks(), function ($policyCheck) {

            return $policyCheck->isPass();
        });
    }

    /**
     * Gets the fail policy check instances.
     *
     * @return array An array containing the fail policy check instances.
     *
     * @throws \Exception
     */
    public function getFailChecks()
    {
        return array_filter($this->getChecks(), function ($policyCheck) {

            return $policyCheck->isFail();
        });
    }

    /**
     * Returns an array containing result messages of fail checks.
     *
     * @return array
     *   An array of messages.
     *
     * @throws \Exception
     */
    public function getFailChecksResultMessages()
    {
        $messages = [];

        /** @var \EdisonLabs\PolicyVerification\Check\PolicyCheckInterface $policyCheck */
        foreach ($this->getFailChecks() as $policyCheck) {
            $messages[] = $policyCheck->getResultFailMessage();
        }

        return $messages;
    }

    /**
     * Returns an array containing action messages of failed policy checks.
     *
     * @param bool $namePrefix A boolean indicating to include the policy name prefix in the messages or not.
     *
     * @return array
     *   An array of action messages.
     *
     * @throws \Exception
     */
    public function getFailChecksActions($namePrefix = false)
    {
        $messages = [];

        /** @var \EdisonLabs\PolicyVerification\Check\PolicyCheckInterface $policyCheck */
        foreach ($this->getFailChecks() as $policyCheck) {
            $prefix = $namePrefix ? $policyCheck->getName().': ' : '';
            $actions = $policyCheck->getActions();

            foreach ($actions as $action) {
                $messages[] = $prefix.$action;
            }
        }

        return $messages;
    }

  /**
   * Returns an array containing warning messages from policy checks.
   *
   * @param bool $namePrefix A boolean indicating to include the policy name prefix in the message or not.
   *
   * @return array
   *   An array of messages.
   *
   * @throws \Exception
   */
    public function getWarningMessages($namePrefix = false)
    {
        $messages = [];

        /** @var \EdisonLabs\PolicyVerification\Check\PolicyCheckInterface $policyCheck */
        foreach ($this->getChecks() as $policyCheck) {
            $message = $policyCheck->getWarningMessage();
            if (!$message) {
                continue;
            }

            $prefix = $namePrefix ? $policyCheck->getName().': ' : '';
            $messages[] = $prefix.$message;
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
        $totalFailChecks = count($this->getFailChecks());

        return ($totalChecks - $totalFailChecks);
    }

    /**
     * Gets the score percentage for fail checks.
     *
     * @return int The score percentage.
     *
     * @throws \Exception
     */
    public function getScorePercentage()
    {
        $totalChecks = $this->getTotalChecks();

        if (!$totalChecks) {
            return 0;
        }

        $totalPassChecks = count($this->getPassChecks());

        return round((100 * $totalPassChecks) / $totalChecks);
    }

    /**
     * Gets the report result.
     *
     * @return bool True if pass, false if fail.
     *
     * @throws \Exception
     */
    public function getResult()
    {
        if (count($this->getFailChecks()) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Converts a string to a machine name.
     *
     * @param mixed $value The value to be transformed.
     *
     * @return string The newly transformed value.
     */
    public function convertToMachineName($value)
    {
        $newValue = strtolower($value);
        $newValue = preg_replace('/[^a-z0-9_]+/', '_', $newValue);
        $newValue = preg_replace('/_+/', '_', $newValue);

        return $newValue;
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
            'data' => $this->getData(),
            'timestamp' => time(),
            'result' => $this->getResult(),
            'summary' => [
                'total' => $this->getTotalChecks(),
                'total_pass' => count($this->getPassChecks()),
                'total_fail' => count($this->getFailChecks()),
                'percentage_pass' => $this->getScorePercentage(),
            ],
            'policies' => [],
        ];

        // Include summary of messages.
        $summary['messages'] = [
            'fail' => $this->getFailChecksResultMessages(),
            'action' => $this->getFailChecksActions(),
            'warning' => $this->getWarningMessages(),
        ];

        // Makes fail checks be listed first.
        $checks = $this->getFailChecks() + $this->getPassChecks();

        /** @var \EdisonLabs\PolicyVerification\Check\PolicyCheckInterface $policyCheck */
        foreach ($checks as $policyCheck) {
            $categoryMachineName = $this->convertToMachineName($policyCheck->getCategory());
            $policyMachineName = $this->convertToMachineName($policyCheck->getName());
            $summary['policies'][$categoryMachineName][$policyMachineName] = [
                'name' => $policyCheck->getName(),
                'description' => $policyCheck->getDescription(),
                'category' => $policyCheck->getCategory(),
                'result' => $policyCheck->getResult(),
                'message' => $policyCheck->getResultMessage(),
                'message_warning' => $policyCheck->getWarningMessage(),
                'actions' => $policyCheck->isFail() ? $policyCheck->getActions() : [],
                'severity' => $policyCheck->getSeverity(),
            ];
        }

        return $summary;
    }
}
