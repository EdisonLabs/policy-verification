<?php

namespace EdisonLabs\PolicyVerification\Check;

/**
 * Defines and interface for Policy checks.
 *
 * @package EdisonLabs\PolicyVerification\Check
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
     * Gets the risk level for the policy check.
     *
     * @return string Constants indicating the risks low, medium, high or critical.
     */
    public function getRiskLevel();

    /**
     * Performs the policy check.
     *
     * @return int Constants indicating whether the policy is compliant or not.
     */
    public function check();

    /**
     * Gets a description of what happened in a passed check.
     *
     * @return string The policy compliant check result message.
     */
    public function getResultCompliantMessage();

    /**
     * Gets the description of what happened in a failed check.
     *
     * @return string The policy not compliant check result message.
     */
    public function getResultNotCompliantMessage();

    /**
     * Gets action items to perform if the check did not pass.
     *
     * @return array Returns a list containing the action messages.
     */
    public function getActions();
}
