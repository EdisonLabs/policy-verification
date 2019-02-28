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
     * An array containing action messages.
     *
     * @var array
     */
    protected $actions = [];

    /**
     * An array containing warning messages.
     *
     * @var array
     */
    protected $warnings = [];

    /**
     * An array containing check requirement error messages.
     *
     * @var array
     */
    protected $requirementErrors = [];

    /**
     * A boolean indicating whether the requirements were checked.
     *
     * @var bool
     */
    protected $requirementsChecked = false;

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
    abstract public function check();

    /**
     * {@inheritdoc}
     */
    public function setData(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Marks requirements as checked.
     */
    public function markRequirementsAsChecked()
    {
        $this->requirementsChecked = true;
    }

    /**
     * {@inheritdoc}
     */
    public function checkRequirements()
    {
        // No requirement checks by default.
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequirementError($error)
    {
        $this->requirementErrors[] = $error;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequirementErrors(array $errors)
    {
        foreach ($errors as $error) {
            $this->setRequirementError($error);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRequirementErrors()
    {
        if (!$this->requirementsChecked) {
            $this->checkRequirements();
            $this->markRequirementsAsChecked();
        }

        return $this->requirementErrors;
    }

    /**
     * {@inheritdoc}
     */
    public function setWarning($warning)
    {
        $this->warnings[] = $warning;
    }

    /**
     * {@inheritdoc}
     */
    public function setWarnings(array $warnings)
    {
        foreach ($warnings as $warning) {
            $this->setWarning($warning);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

    /**
     * {@inheritdoc}
     */
    public function setAction($action)
    {
        $this->actions[] = $action;
    }

    /**
     * {@inheritdoc}
     */
    public function setActions(array $actions)
    {
        foreach ($actions as $action) {
            $this->setAction($action);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Gets the policy check result.
     *
     * @return bool Constants indicating pass or fail.
     */
    public function getResult()
    {
        // Fail the check if there are requirement errors.
        if ($this->getRequirementErrors()) {
            $this->result = self::POLICY_FAIL;

            return $this->result;
        }

        if (is_null($this->result)) {
            $this->result = $this->check();
        }

        return $this->result;
    }

    /**
     * Returns the check result message when there are requirement errors.
     *
     * @return string The result message.
     */
    public function getResultRequirementErrorMessage()
    {
        return 'Could not proceed with policy verification due to requirement errors';
    }

    /**
     * Gets the description of what happened in the check.
     *
     * @return string The policy check result message.
     */
    public function getResultMessage()
    {
        if ($this->getRequirementErrors()) {
            return $this->getResultRequirementErrorMessage();
        }

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
}
