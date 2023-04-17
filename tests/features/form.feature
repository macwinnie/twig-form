Feature: Form
  In order to gather user data for filling a Template
  As a GUI user
  I need to be able to fill in values into a form
  And
  As a developer of PHP tools using Twig
  I need to validate these values

  @form
  Scenario: Test availability of helper endpoint
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

  @form
  Scenario: Check if template is translated to JSON form
    Given I have the payload
      """
      {
        "template": "Lorem ipsum {{ dolor }} sit {{ amet }}"
      }
      """
    When I request "POST /tests/helper/"
    Then I should see a JSON response
    And the JSON should contain not-NULL key-tree "create"
    And the JSON should have value "twigform" at key-tree "create.id"
    And the JSON should contain key-tree "rows" with "2" sub-elements
    And the JSON should have value "dolor" at key-tree "rows.0.name"
    And the JSON should have value "amet" at key-tree "rows.1.name"
    And the JSON should have value "text" at key-tree "rows.1.type"

  @form
  Scenario: Check if named template returns named form
    Given I have the payload
      """
      {
        "template": "Lorem ipsum {{ dolor }} sit {{ amet }}",
        "formid":   "lorem_test"
      }
      """
    When I request "POST /tests/helper/"
    Then I should see a JSON response
    And the JSON should have value "lorem_test" at key-tree "create.id"
    And the JSON should contain key-tree "rows" with "2" sub-elements
    And the JSON should have value "dolor" at key-tree "rows.*.name"
    And the JSON should have value "amet" at key-tree "rows.*.name"

  @form
  Scenario: One form should be returned even if block is used
    Given I have the payload
      """
      {
        "template": "Lorem ipsum {{ dolor }} {% block sit %}{{ amet }}{% endblock %}"
      }
      """
    When I request "POST /tests/helper/"
    Then I should see a JSON response
    And the JSON should have value "twigform" at key-tree "create.id"
    And the JSON should contain key-tree "create" with "3" sub-elements
    And the JSON should contain key-tree "rows" with "2" sub-elements
    And the JSON should have value "text" at key-tree "rows.0.type"
    And the JSON should have value "text" at key-tree "rows.1.type"
    And the JSON should have value "dolor" at key-tree "rows.*.name"
    And the JSON should have value "amet" at key-tree "rows.*.name"
