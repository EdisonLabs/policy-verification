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
     * Performs the policy check.
     *
     * @return bool Constants indicating pass or fail.
     */
    public function check();

    /**
     * Gets a description of what happened in a pass check.
     *
     * @return string The policy check result pass message.
     */
    public function getResultPassMessage();

    /**
     * Gets the description of what happened in a fail check.
     *
     * @return string The policy check result fail message.
     */
    public function getResultFailMessage();

    /**
     * Returns a warning message regardless of the check result.
     *
     * @return string The policy check result warning message.
     */
    public function getWarningMessage();

    /**
     * Gets action items to perform if the check did not pass.
     *
     * @return array Returns a list containing the action messages.
     */
    public function getActions();
}
