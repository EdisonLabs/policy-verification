[![Build Status](https://travis-ci.com/EdisonLabs/policy-verification.svg?branch=1.x)](https://travis-ci.com/EdisonLabs/policy-verification)
[![Coverage Status](https://coveralls.io/repos/github/EdisonLabs/policy-verification/badge.svg?branch=1.x)](https://coveralls.io/github/EdisonLabs/policy-verification?branch=1.x)

# Policy verification

## Overview
Policy verification is a simple library that provides base classes for creating and reporting security policy checks.

Each policy check returns as a result whether the policy is compliant or not.
Other information is also reported like the actions to be taken if the policy is not compliant.

## Usage
This library does not provide any policy check by default. To create new checks, [create a new Composer package](https://getcomposer.org/doc/01-basic-usage.md) and add a dependency to it.

```
composer require edisonlabs/policy-verification
```

Now create the policy check classes extending the base class provided by the library.

The classes must be created at `/src/EdisonLabs/PolicyVerification`.
This is a requirement for the library to automagically locate and perform the checks during the report generation.

```php
// File: /src/EdisonLabs/PolicyVerification/PhpVersion.php

namespace EdisonLabs\PolicyVerification;

use EdisonLabs\PolicyVerification\Check\AbstractPolicyCheckBase;

class PhpVersion extends AbstractPolicyCheckBase
{
    public function getName()
    {
        return 'PHP version';
    }

    public function getDescription()
    {
        return 'Checks whether system is running a recent version of PHP';
    }

    public function getCategory()
    {
        return 'PHP';
    }

    public function getRiskLevel()
    {
        return parent::POLICY_RISK_HIGH;
    }

    public function getResultCompliantMessage()
    {
        return 'The system is running a recent version of PHP';
    }

    public function getResultNotCompliantMessage()
    {
        return 'The system is running an older version of PHP';
    }

    public function getActions()
    {
        return [
            'Upgrade to PHP7 or greater'
        ];
    }

    public function check()
    {
        $phpVersion = phpversion();

        if ($phpVersion[0] < 7) {
            return parent::POLICY_NOT_COMPLIANT;
        }

        return parent::POLICY_COMPLIANT;
    }
}
```

Configure the autoload in `composer.json`.
```
"autoload": {
    "psr-4": {
        "EdisonLabs\\PolicyVerification\\": "src/EdisonLabs/PolicyVerification"
    }
}
```

Re-create the Composer autoloader.
```
composer dump-autoload
```

## Report

There are two ways to generate the policy check results report: programmatically and/or by command-line.

### Programmatically
```php
use EdisonLabs\PolicyVerification\Report;

// Some custom data to pass to the policy checks.
$data = array();

$report = new Report($data);

// Prints the result summary.
print_r($report->getResultSummary());

// Other report methods.
$report->getChecks();
$report->getCompliantChecks();
$report->getCompliantScorePercentage();
$report->getData();
$report->getNonCompliantChecks();
$report->getNonCompliantChecksActions();
$report->getNonCompliantChecksResultMessages();
$report->getResult();
$report->getResultSummary();
$report->getScore();
$report->getTotalChecks();
$report->setCheck();
```

### Command
The command is located at `vendor/bin/policy-verification`. Include the `vendor/bin` directory in the system `$PATH` to run this command from anywhere.

Type `policy-verification --help` to see all the available options.
