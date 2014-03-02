# Simple smoke test to check the front page responds correctly
# and shows a working login form.

Feature: front page
  In order to access the website
  As an anonymous user
  I need to be able to get the login form

  Scenario: Submits credentials when required fields are not filled out
    Given I am on "/user/login"
    And the response status code should be 200
    When I press "Log in"
    Then I should see "Username field is required."
    And I should see "Password field is required."

  Scenario: Submits credentials when required fields are filled out
    Given I am on "/user/login"
    And the response status code should be 200
    When I fill in "Username" with "italy"
    And I fill in "Password" with "italy"
    And I press "Log in"
    Then I should see "italy"

