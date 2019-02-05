<?php

namespace EdisonLabs\PolicyVerification\Check;

/**
 * Defines a base class for Policy checks.
 *
 * @package EdisonLabs\PolicyVerification\Check
 */
abstract class AbstractPolicyCheckBase implements PolicyCheckInterface
{
    const POLICY_COMPLIANT = 1;
    const POLICY_NOT_COMPLIANT = 0;
    const POLICY_RISK_LOW = 'low';
    const POLICY_RISK_MEDIUM = 'medium';
    const POLICY_RISK_HIGH = 'high';
    const POLICY_RISK_CRITICAL = 'critical';

    /**
     * Data passed in for checks and report.
     *
     * @var array
     */
    protected $data = [];

    /**
     * The policy check result constant, compliant or not compliant.
     *
     * @var int
     */
    protected $result = null;

    /**
     * AbstractPolicyCheckBase constructor.
     *
     * @param array $data An array containing data to be passed in for checks and reports.
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getName();

    /**
     * {@inheritdoc}
     */
    abstract public function getDescription();

    /**
     * {@inheritdoc}
     */
    abstract public function getCategory();

    /**
     * {@inheritdoc}
     */
    abstract public function getRiskLevel();

    /**
     * {@inheritdoc}
     */
    abstract public function getResultCompliantMessage();

    /**
     * {@inheritdoc}
     */
    abstract public function getResultNotCompliantMessage();

    /**
     * {@inheritdoc}
     */
    abstract public function getActions();

    /**
     * Gets the policy check result.
     *
     * @return int Constants indicating whether the policy is compliant or not.
     */
    public function getResult()
    {
        if (is_null($this->result)) {
            $this->result = $this->check();
        }

        return $this->result;
    }

    /**
     * Gets the description of what happened in the check.
     *
     * @return string The policy check result message.
     */
    public function getResultMessage()
    {
        if ($this->getResult() == self::POLICY_COMPLIANT) {
            return $this->getResultCompliantMessage();
        }

        return $this->getResultNotCompliantMessage();
    }

    /**
     * Checks whether policy is compliant.
     *
     * @return bool TRUE in case of success, FALSE otherwise.
     */
    public function isCompliant()
    {
        return $this->getResult() == self::POLICY_COMPLIANT;
    }

    /**
     * Checks whether policy is not compliant.
     *
     * @return bool TRUE in case of success, FALSE otherwise.
     */
    public function isNotCompliant()
    {
        return $this->getResult() == self::POLICY_NOT_COMPLIANT;
    }

    /**
     * Returns the policy data array.
     *
     * @return array The policy data.
     */
    public function getData()
    {
        return $this->data;
    }
}
