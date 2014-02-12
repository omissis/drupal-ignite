<?php

use Drupal\DrupalExtension\Context\DrupalContext;
use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

require 'vendor/autoload.php';

/**
 * Features context for custom step-definitions.
 *
 * @todo we are duplicating code from Behat's FeatureContext here for the
 * purposes of testing since we can't easily run that as a subcontext due to
 * naming conflicts.
 */
class FeatureContext extends DrupalContext
{
    /**
     * Initialize the needed step definitions for subcontext testing.
     */
    public function __construct(array $parameters)
    {
        if (isset($parameters['username'])) {
            $this->username = $parameters['username'];
        }
        if (isset($parameters['password'])) {
            $this->password = $parameters['password'];
        }
        if (isset($parameters['requires'])) {
            $this->requires = $parameters['requires'];
        }
        $this->useContext('behat_feature_context', new BehatFeatureContext());
    }


    /**
     * @BeforeScenario
     */
    public function setUpScenario()
    {
        if (!empty($this->username) || !empty($this->password)) {
            $this->getSession()->setBasicAuth($this->username, $this->password);
        }
    }

    /**
     * @BeforeScenario @api
     */
    public function setUpApiScenario()
    {
        if (!isset($this->requires) || empty($this->requires)) {
            return;
        }

        foreach ($this->requires as $module) {
            $result = $this->getDriver()->drush('pmi', array($module));
            if (false === strstr($result, 'Status           :  enabled')) {
                throw new \RuntimeException(sprintf("You need to enable module '%s' to run this tests", $module));
            }
        }
    }

    /**
     * @AfterFeature
     */
    public static function tearDownFeature(FeatureEvent $event)
    {
        // TODO: implement DB cleanup
    }

    /**
     * Creates and authenticates a user with the given role via Drush.
     *
     * @Given /^I am logged in with username "(?P<username>[^"]*)" and password "(?P<password>[^"]*)"$/
     */
    public function iAmLoggedInWithUsernameAndPassword($username, $password)
    {
        // Check if a user with this role is already logged in.
        if ($this->loggedIn() && $this->user && isset($this->user->name) && $this->user->name == $username) {
            return true;
        }

        // Create user (and project)
        $user = (object) array(
            'name' => $username,
            'pass' => $password,
            'role' => 'authenticated user',
        );
        $user->mail = "{$user->name}@example.com";

        $this->users[] = $this->user = $user;

        // Login.
        $this->login();

        return true;
    }

    /**
     * Opens page for the latest node of a given content-type created by the given user.
     *
     * @Given /^(?:|I )am on the latest ([^"]*) created by "([^"]*)"$/
     * @When /^(?:|I )go to the latest ([^"]*) created by "([^"]*)"$/
     */
    public function iAmOnTheLatestNodeCreatedBy($contentType, $username)
    {
        $nid = $this->getIdOfTheLatestNode($contentType, $username);

        $this->getSession()->visit($this->locatePath("node/$nid"));
    }

    /**
     * @Then /^I should not see the link "([^"]*)" in the "([^"]*)" region$/
     */
    public function iShouldNotSeeTheLinkInTheRegion($link, $region)
    {
        $element = $this->getSession()->getPage()->find('region', $region);
        if (!$element) {
            throw new \Exception(
                sprintf('No region "%s" found on the page %s.', $region, $this->getSession()->getCurrentUrl())
            );
        }
        $result = $element->findLink($link);
        if (!empty($result)) {
            throw new \Exception(
                sprintf(
                    'The link "%s" was present in the "%s" region on the page %s and was not supposed to be',
                    $link,
                    $region,
                    $this->getSession()->getCurrentUrl()
                )
            );
        }
    }

    /**
     * @Then /^I should not see the "([^"]*)" element$/
     */
    public function iShouldNotSeeTheElement($selector)
    {
        $element = $this->getSession()->getPage()->find('css', $selector);

        if (!empty($element)) {
            throw new \Exception(
                sprintf(
                    'The element "%s" was present on the page %s and was not supposed to be',
                    $selector,
                    $this->getSession()->getCurrentUrl()
                )
            );
        }
    }

    /**
     * @Then /^I should not see the "([^"]*)" region$/
     */
    public function iShouldNotSeeTheRegion($region)
    {
        $element = $this->getSession()->getPage()->find('region', $region);
        if ($element) {
            throw new \Exception(
                sprintf(
                    'region "%s" found on the page %s and was not supposed to be',
                    $region,
                    $this->getSession()->getCurrentUrl()
                )
            );
        }
    }

    /**
     * Checks if the current page contains the given success message regex
     *
     * @param $messageRegex
     *   string The tegex to be checked
     *
     * @Then /^I should see the success regex message(?:| messageRegex) "([^"]*)"$/
     */
    public function iShouldSeeTheSuccessRegexMessage($messageRegex)
    {
        $successSelector = $this->getDrupalSelector('success_message_selector');
        $successSelectorObj = $this->getSession()->getPage()->find("css", $successSelector);
        if (empty($successSelectorObj)) {
            throw new \Exception(
                sprintf("The page '%s' does not contain any success messages", $this->getSession()->getCurrentUrl())
            );
        }
        if (1 !== preg_match("/$messageRegex/", trim($successSelectorObj->getText()))) {
            throw new \Exception(
                sprintf(
                    "The page '%s' does not contain the success message '%s'",
                    $this->getSession()->getCurrentUrl(),
                    $messageRegex
                )
            );
        }
    }

    /**
     * @Then /^I (?:|should )see the heading regex "(?P<heading>[^"]*)"$/
     */
    public function iShouldSeeTheHeadingRegex($heading)
    {
        $element = $this->getSession()->getPage();
        foreach (array('h1', 'h2', 'h3', 'h4', 'h5', 'h6') as $tag) {
            $results = $element->findAll('css', $tag);
            foreach ($results as $result) {
                if (1 !== preg_match("/$heading/", trim($result->getText()))) {
                    return;
                }
            }
        }
        throw new \Exception(
            sprintf(
                "The text '%s' was not found in any heading on the page %s",
                $heading,
                $this->getSession()->getCurrentUrl()
            )
        );
    }

    /**
     * Find a heading in a specific region.
     *
     * @Then /^I should see the heading regex "(?P<heading>[^"]*)" in the "(?P<region>[^"]*)"(?:| region)$/
     * @Then /^I should see the "(?P<heading>[^"]*)" heading regex in the "(?P<region>[^"]*)"(?:| region)$/
     */
    public function iShouldSeeTheHeadingRegexInTheRegion($heading, $region)
    {
        $page = $this->getSession()->getPage();
        $regionObj = $page->find('region', $region);
        if (!$regionObj) {
            throw new \Exception(
                sprintf('No region "%s" found on the page %s.', $region, $this->getSession()->getCurrentUrl())
            );
        }

        foreach (array('h1', 'h2', 'h3', 'h4', 'h5', 'h6') as $tag) {
            $elements = $regionObj->findAll('css', $tag);
            if (!empty($elements)) {
                foreach ($elements as $element) {
                    if (1 !== preg_match("/$heading/", trim($element->getText()))) {
                        return;
                    }
                }
            }
        }

        throw new \Exception(
            sprintf(
                'The heading "%s" was not found in the "%s" region on the page %s',
                $heading,
                $region,
                $this->getSession()->getCurrentUrl()
            )
        );
    }

    /**
     * @Then /^I should see the latest ([^"]*) created by "([^"]*)"$/
     */
    public function iShouldSeeTheLatestNodeCreatedBy($contentType, $username)
    {
        $this->iAmOnTheLatestNodeCreatedBy($contentType, $username);
        $this->assertSession()->statusCodeEquals("200");
    }

    /**
     * @Then /^I should not see the latest ([^"]*) created by "([^"]*)"$/
     */
    public function iShouldNotSeeTheLatestNodeCreatedBy($contentType, $username)
    {
        $this->iAmOnTheLatestNodeCreatedBy($contentType, $username);
        $this->assertSession()->statusCodeEquals("403");
    }

    /**
     * @Then /^I delete the latest ([^"]*) created by "([^"]*)"$/
     */
    public function iDeleteTheLatestNodeCreatedBy($contentType, $username)
    {
        $nid = $this->getIdOfTheLatestNode($contentType, $username);
        $this->getDriver()->drush('eval', array('"node_delete(' . $nid . ');"'));
    }

    /**
     * Helper function to login the current user.
     */
    public function login()
    {
        // Check if logged in.
        if ($this->loggedIn()) {
            $this->logout();
        }

        if (!$this->user) {
            throw new \Exception('Tried to login without a user.');
        }

        $this->getSession()->visit($this->locatePath('/user'));
        $element = $this->getSession()->getPage();
        $element->fillField('Nome utente', $this->user->name);
        $element->fillField('Password', $this->user->pass);
        $submit = $element->findButton('Accedi');
        if (empty($submit)) {
            throw new \Exception(sprintf("No submit button at %s", $this->getSession()->getCurrentUrl()));
        }

        // Log in.
        $submit->click();

        if (!$this->loggedIn()) {
            throw new \Exception(
                sprintf("Failed to log in as user '%s' with password '%s'", $this->user->name, $this->user->pass)
            );
        }
    }

    /**
     * Determine if the a user is already logged in.
     */
    public function loggedIn($message = 'Log out')
    {
        $session = $this->getSession();
        $session->visit($this->locatePath('/'));

        // If a logout link is found, we are logged in. While not perfect, this is
        // how Drupal SimpleTests currently work as well.
        $element = $session->getPage();
        return $element->findLink($message);
    }

    /**
     * Get Id of the latest node, optionally by type and author
     *
     * @param string $type
     * @param string $username
     *
     * @return int node id
     */
    protected function getIdOfTheLatestNode($type = null, $username = null)
    {
        $wheres = array();

        if (!empty($type)) {
            $wheres[] = "type='$type'";
        }
        if (!empty($username)) {
            $userInfo = $this->getDriver()->userInformation($username);
            $uid = $userInfo['id'];
            $wheres[] = "uid='$uid'";
        }

        $arguments = sprintf(
            '"SELECT nid FROM node %s ORDER BY created DESC LIMIT 1"',
            count($wheres) ? 'WHERE ' . implode(' AND ', $wheres) : ''
        );

        $result = $this->getDriver()->drush('sql-query', $arguments);

        $rows = explode("\n", trim($result));

        return intval($rows[1]);
    }

    /******************************************************************************************************************/
    /*********************************************** MAIL METHODS *****************************************************/
    /******************************************************************************************************************/

    /**
     * @BeforeScenario @mail
     */
    public function setUpMailScenario()
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
    public function tearDownMailScenario()
    {
        // reset the default mailing system
        variable_set('mail_system', $this->defaultMailSystem);
    }

    /**
     * @Given /^the email message with subject "([^"]*)" was sent to the following email addresses:$/
     */
    public function wasMessageSentToEmails($subject, array $emails)
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
    public function countSentEmails($emailCount)
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
    public function castEmailsTable(TableNode $emailsTable)
    {
        $emails = array();
        foreach ($emailsTable->getHash() as $emailHash) {
            $emails[] = $emailHash['email'];
        }

        return $emails;
    }

    protected function getSentEmails()
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

    protected function clearWatchdog()
    {
        return $this->getDriver()->drush('sql-query', array('"TRUNCATE TABLE watchdog"'));
    }
}
