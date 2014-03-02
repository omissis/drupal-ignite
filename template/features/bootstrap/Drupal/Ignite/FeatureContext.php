<?php

namespace Drupal\Ignite;

use Drupal\DrupalExtension\Context\DrupalContext;
use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Event\FeatureEvent;

class FeatureContext extends DrupalContext
{
    /**
     * Initialize the needed step definitions for subcontext testing.
     */
    public function __construct(array $parameters = array())
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
        $this->useContext('ignite_email_context', new EmailContext());
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
        $user->mail = "{$user->name}@drupal-ci.com";

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

        $this->getSession()->visit($this->locatePath('/user/login'));
        $element = $this->getSession()->getPage();
        $element->fillField('Username', $this->user->name);
        $element->fillField('Password', $this->user->pass);
        $submit = $element->findButton('Log in');
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
            $userInfo = $this->getUserInformation($username);
            $uid = $userInfo['id'];
            $wheres[] = "uid='$uid'";
        }

        $arguments = array(
            sprintf(
                '"SELECT nid FROM node %s ORDER BY created DESC LIMIT 1"',
                count($wheres) ? 'WHERE ' . implode(' AND ', $wheres) : ''
            )
        );

        $result = $this->getDriver()->drush('sql-query', $arguments);

        $rows = explode("\n", trim($result));

        return intval($rows[1]);
    }

    /**
     * Implements missing drivers' missing userInformation() method.
     */
    protected function getUserInformation($username) {
        $arguments = array(
          "\"$username\"",
        );
        $options = array();

        $result = $this->getDriver()->drush('user-information', $arguments, $options);

        $rows = explode("\r\n", trim($result));
        $fields = array();

        foreach ($rows as $rid => $row) {
            $field = explode(':', $row);
            $fields[strtolower(trim(str_replace('User ', '', $field[0])))] = trim($field[1]);
        }

        return $fields;
    }
}
