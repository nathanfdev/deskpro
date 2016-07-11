@new
Feature: Custom fields
  I want to check text/texarea validation

  Background:
    Given I'm authenticated as admin

  Scenario Outline: I check toggle should be checked
    Given only the following custom ticket fields exist:
      | # | Type   | Title        | Options            |
      | t | toggle | Toggle field | {"<option>": true} |
    And the only default ticket layout exists with fields:
      | <context>_layout |
      | ticket_field_{t} |

    When I send a POST request to "/api/v2/ticket_forms/<context>" with body:
    """
{
  "fields": {
    "~t~": ""
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_{t}.errors[0].code" should be equal to "not_checked"

    Examples:
      | context | option                |
      | agent   | agent_validation_type |
      | user    | validation_type       |
