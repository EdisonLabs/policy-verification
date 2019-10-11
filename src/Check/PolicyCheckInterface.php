<?php

namespace EdisonLabs\PolicyVerification\Check;

/**
 * Defines and interface for Policy checks.
 */
interface PolicyCheckInterface
{

  /**
   * Gets a human readable name for the policy check.
   *
   * @return string The policy check name.
   */
    public function getName();

    /**
     * Gets a human readable description for the policy check.
     *
     * @return string The policy check description.
     */
    public function getDescription();

    /**
     * Gets a human readable category name for the policy check.
     *
     * @return string The policy check category name.
     */
    public function getCategory();

    /**
     * Gets the severity for the policy check.
     *
     * @return string Constants indicating the severity low, medium, high or critical.
     */
    public function getSeverity();

    /**
     * Sets the result pass message for the policy check.
     *
     * @param string $message The result message.
     */
    public function setResultPassMessage($message);

    /**
     * Gets a description of what happened in a pass check.
     *
     * @return string The policy check result pass message.
     */
    public function getResultPassMessage();

    /**
     * Sets the result fail message for the policy check.
     *
     * @param string $message The result message.
     */
    public function setResultFailMessage($message);

    /**
     * Gets the description of what happened in a fail check.
     *
     * @return string The policy check result fail message.
     */
    public function getResultFailMessage();

    /**
     * Sets the data array.
     *
     * @param array $data The policy data.
     */
    public function setData(array $data = []);

    /**
     * Returns the data array.
     *
     * @return array The policy data.
     */
    public function getData();

    /**
     * Performs the requirements verification.
     */
    public function checkRequirements();

    /**
     * Sets a check requirement error message.
     *
     * @param string $error The error message.
     */
    public function setRequirementError($error);

    /**
     * Sets multiple check requirement error messages.
     *
     * @param array $errors An array of requirement error messages.
     */
    public function setRequirementErrors(array $errors);

    /**
     * Gets the check requirement error messages.
     *
     * @return array Returns a list containing the requirement error messages.
     *
     * @see checkRequirements()
     */
    public function getRequirementErrors();

    /**
     * Performs the policy check.
     *
     * @return bool Constants indicating pass or fail.
     */
    public function check();

    /**
     * Returns whether to skip the check or not.
     *
     * @return bool The flag to to skip or not the check.
     */
    public function skipCheck();

    /**
     * Sets a warning message.
     *
     * @param string $warning The warning message.
     */
    public function setWarning($warning);

    /**
     * Sets multiple warning messages.
     *
     * @param array $warnings An array of warning messages.
     */
    public function setWarnings(array $warnings);

    /**
     * Returns warning messages regardless of the check result.
     *
     * @return array The policy check result warning messages.
     */
    public function getWarnings();

    /**
     * Sets a action message.
     *
     * @param string $action The action message.
     */
    public function setAction($action);

    /**
     * Sets multiple action messages.
     *
     * @param array $actions An array of messages.
     */
    public function setActions(array $actions);

    /**
     * Gets action items to perform if the check did not pass.
     *
     * @return array Returns a list containing the action messages.
     */
    public function getActions();
}
