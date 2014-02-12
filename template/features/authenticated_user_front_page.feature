# Simple smoke test to check the front page responds correctly
# and shows a working login form.

Feature: front page
  In order to access the website
  As an anonymous user
  I need to be able to get the login form

  @javascript
  Scenario: Submits credentials when required fields are not filled out
    Given I am on "/"
    And the response status code should be 200
    When I press "Log In"
    Then I should see "Username field is required."
    And I should see "Password field is required."

  @javascript
  Scenario: Submits credentials when required fields are filled out
    Given the following people exist:
      | name  | email         | phone |
      | Foo   | foo@email.com | 123   |
      | Bar   | bar@email.com | 234   |
      | Baz   | baz@email.org | 456   |
    And I am on "/"
    And the response status code should be 200
    When I fill in "Username" with "admin"
    And I fill in "Password" with "admin"
    And I press "Log In"
    Then I should see "some text"
