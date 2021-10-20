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
  Scenario: Check if form JSON can be rendered as HTML form
    Given I have the payload
      """
      {
        "json2form": "{\"create\":{\"action\":\"\/tests\/helper\/?\"},\"buttons\":[{\"text\":\"submit\",\"class\":\"btn btn-primary\",\"name\":\"submitbutton\",\"value\":\"submit_val\"}],\"rows\":[{\"name\":\"text\",\"placeholder\":\"ph text\",\"type\":\"text\",\"title\":\"single line textfield\"}]}"
      }
      """
    When I request "POST /tests/helper/"
    Then There should be a "h1" tag with text "JSON to HTML form"
    And There should be a "input" tag with attribute "name" and value "text"
    And There should be a "input" tag with attribute "placeholder" and value "ph text"
    And There should be a "button" tag with attribute "name" and value "submitbutton"

  @form @skip
  Scenario: Check if template is translated to JSON form
    Given I have the payload
      """
      {
        "template": "Lorem ipsum {{ dolor }} sit {{ amet }}"
      }
      """
    When I request "POST /tests/helper/"
    Then I should see a JSON response