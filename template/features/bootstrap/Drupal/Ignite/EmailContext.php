<?php

namespace Drupal\Ignite;

use Behat\MinkExtension\Context\RawMinkContext;

class EmailContext 
                    extends RawMinkContext
{
    /**
     * @BeforeScenario @mail
     */
    public 
            function setUpMailScenario
                                        ()
    {
        // save the default mailing system
        $this->defaultMailSystem = variable_get('mail_system');

        // set the secoval debug mailing system
        variable_set('mail_system', array('default-system' => 'WatchdogMailSystem'));

        // clear the watchdog table
        $this->clearWatchdog();
    }

    /**
     * @AfterScenario @mail
     */
    public 
            function 
                    tearDownMailScenario
                                        ()
    {
        // reset the default mailing system
        variable_set('mail_system', $this->defaultMailSystem);
    }

    /**
     * @Given /^the email message with subject "([^"]*)" was sent to the following email addresses:$/
     */
    public 
            function 
                    wasMessageSentToEmails
                                            ($subject, array $emails)
    {
        $sentEmails = $this->getSentEmails();
        $subject = sprintf($subject, $this->getIdOfTheLatestNode());

        foreach ($sentEmails as $sentEmail) {
            if (false === strstr($sentEmail['message'], $subject)) {
                throw new \Exception(sprintf('subject "%s" not found in "%s"', $subject, $sentEmail['message']));
            }
            $found = false;
            foreach ($emails as $email) {
                if (false !== strstr($sentEmail['message'], $email)) {
                    $found = true;
                    break;
                }
            }
            if (false === $found) {
                throw new \Exception(
                    sprintf('email has not been sent to any address in the list. Message:"', $sentEmail['message'])
                );
            }
        }
    }

    /**
     * @Given /^a total of (\d+) emails have been sent$/
     */
    public 
            function 
                    countSentEmails
                                    ($emailCount)
    {
        $sentEmails = $this->getSentEmails();

        if (count($sentEmails) != $emailCount) {
            $matches = array();
            foreach ($sentEmails as $sentEmail) {
                $start = strpos($sentEmail['message'], 'Originally to: ') + strlen('Originally to: ');
                $end = strpos($sentEmail['message'], '\\n', $start);
                $matches[] = substr($sentEmail['message'], $start, $end - $start);
            }
            throw new \Exception(
                sprintf(
                    '%d emails have been sent instead of %d. Found emails: %s.',
                    count($sentEmails),
                    $emailCount,
                    var_export($matches, true)
                )
            );
        }
    }

    /**
     * @Transform /^table:email$/
     */
    public 
            function 
                    castEmailsTable
                                    (TableNode $emailsTable)
    {
        $emails = array();
        foreach ($emailsTable->getHash() as $emailHash) {
            $emails[] = $emailHash['email'];
        }

        return $emails;
    }

    protected 
            function 
                    getSentEmails()
    {
        $query = '"SELECT * FROM watchdog WHERE type = \'%s\' AND severity = %d"';
        $type = 'email_testing';
        $severity = WATCHDOG_DEBUG;
        $arguments = array(sprintf($query, $type, $severity));

        $result = $this->getDriver()->drush('sql-query', $arguments);

        $rows = explode("\n", trim($result));

        $columnNames = array_shift($rows);
        $columnNames = explode("\t", trim($columnNames));

        foreach ($rows as $key => $row) {
            $rows[$key] = array_combine($columnNames, explode("\t", trim($row)));
        }

        return $rows;
    }

    protected 
            function 
                    clearWatchdog()
    {
        return $this->getDriver()->drush('sql-query', array('"TRUNCATE TABLE watchdog"'));
    }
}
