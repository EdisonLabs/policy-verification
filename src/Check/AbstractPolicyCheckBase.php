<?php

namespace EdisonLabs\PolicyVerification\Check;

/**
 * Defines a base class for Policy checks.
 */
abstract class AbstractPolicyCheckBase implements PolicyCheckInterface
{
    const POLICY_PASS = true;
    const POLICY_FAIL = false;
    const POLICY_SEVERITY_LOW = 'low';
    const POLICY_SEVERITY_MEDIUM = 'medium';
    const POLICY_SEVERITY_HIGH = 'high';
    const POLICY_SEVERITY_CRITICAL = 'critical';

    /**
     * Data passed in for checks.
     *
     * @var array
     */
    protected $data = [];

    /**
     * The policy check result constants pass or fail.
     *
     * @var int
     */
    protected $result = null;

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
    abstract public function getSeverity();

    /**
     * {@inheritdoc}
     */
    abstract public function getResultPassMessage();

    /**
     * {@inheritdoc}
     */
    abstract public function getResultFailMessage();

    /**
     * {@inheritdoc}
     */
    public function getWarningMessage()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getActions();

    /**
     * Gets the policy check result.
     *
     * @return bool Constants indicating pass or fail.
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
        if ($this->getResult() == self::POLICY_PASS) {
            return $this->getResultPassMessage();
        }

        return $this->getResultFailMessage();
    }

    /**
     * Checks whether is a pass policy or not.
     *
     * @return bool TRUE in case of success, FALSE otherwise.
     */
    public function isPass()
    {
        return $this->getResult() == self::POLICY_PASS;
    }

    /**
     * Checks whether is a fail policy or not.
     *
     * @return bool TRUE in case of success, FALSE otherwise.
     */
    public function isFail()
    {
        return $this->getResult() == self::POLICY_FAIL;
    }

    /**
     * Sets the data array.
     *
     * @param array $data The policy data.
     */
    public function setData(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Returns the data array.
     *
     * @return array The policy data.
     */
    public function getData()
    {
        return $this->data;
    }
}
