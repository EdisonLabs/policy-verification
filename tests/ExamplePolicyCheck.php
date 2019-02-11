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
    public function getSeverity()
    {
        return parent::POLICY_SEVERITY_HIGH;
    }

    /**
     * {@inheritdoc}
     */
    public function getResultPassMessage()
    {
        return 'The policy passes';
    }

    /**
     * {@inheritdoc}
     */
    public function getResultFailMessage()
    {
        return 'The policy fails';
    }

    /**
     * {@inheritdoc}
     */
    public function getWarningMessage()
    {
        return 'Just an example of warning message';
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
        $data = $this->getData();

        if (isset($data['pass']) && $data['pass'] === 1) {
            return parent::POLICY_PASS;
        }

        return parent::POLICY_FAIL;
    }
}
