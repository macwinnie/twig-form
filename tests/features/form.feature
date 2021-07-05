Feature: Form
  In order to gather user data for filling a Template
  As a GUI user
  I need to be able to fill in values into a form
  And
  As a developer of PHP tools using Twig
  I need to validate these values

  @form
  Scenario: Simple template variable extract
    Given I am on "/tests/helper"
    Then I should see "Nothing to do."

  @form
  Scenario: Check if template is translated to JSON
    Given I have the payload
      """
      {
        "template": "Lorem ipsum {{ dolor }} sit {{ amet }}"
      }
      """
    When I request "POST /tests/helper/"
    Then I should see a JSON response
