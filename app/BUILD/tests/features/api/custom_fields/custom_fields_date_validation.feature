@new
Feature: Custom fields
  I want to check date/datetime validation

  Background:
    Given I'm authenticated as admin

  Scenario Outline: I check date field format validation
    Given only the following custom ticket fields exist:
      | # | Type   | Title      |
      | t | <type> | Text field |
    And the only default ticket layout exists with fields:
      | agent_layout     |
      | ticket_field_{t} |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "fields": {
    "~t~": "not_valid_datetime"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_{t}.errors[0].code" should be equal to "invalid_data_type"

    Examples:
      | type     |
      | date     |
      | datetime |
