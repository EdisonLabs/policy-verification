<?php

namespace EdisonLabs\PolicyVerification\Test;

use EdisonLabs\PolicyVerification\Check\AbstractPolicyCheckBase;

/**
 * Example of policy check class to be used for tests.
 */
class ExamplePolicyCheck extends AbstractPolicyCheckBase
{

  /**
   * {@inheritdoc}
   */
    public function getName()
    {
        return 'Example policy check';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'This is an example of a policy check';
    }

    /**
     * {@inheritdoc}
     */
    public function getCategory()
    {
        return 'Test';
    }

    /**
     * {@inheritdoc}
     */
    public function getRiskLevel()
    {
        return parent::POLICY_RISK_HIGH;
    }

    /**
     * {@inheritdoc}
     */
    public function getResultCompliantMessage()
    {
        return 'The policy is compliant';
    }

    /**
     * {@inheritdoc}
     */
    public function getResultNotCompliantMessage()
    {
        return 'The policy is not compliant';
    }

    /**
     * {@inheritdoc}
     */
    public function getActions()
    {
        return [
            'Just an action example.',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function check()
    {
        return parent::POLICY_NOT_COMPLIANT;
    }
}
