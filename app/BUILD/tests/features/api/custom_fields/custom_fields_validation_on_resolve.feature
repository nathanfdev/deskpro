@new
Feature: Custom fields
  I want to check validation on resolve

  Background:
    Given I'm authenticated as admin
    And only the following custom ticket fields exist:
      | #  | Type | Title      | Options                                                    |
      | f1 | text | Text field | {"agent_validation_resolve": true, "agent_required": true} |
    And the only default ticket layout exists with fields:
      | agent_layout      |
      | ticket_field_{f1} |

  Scenario: I check skip validation
    Given only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "fields": {
    "~f1~": ""
  }
}
    """
    Then the response status code should be 204

  Scenario: I check validation on resolve
    Given only the following Ticket records exist:
      | #  | Subject  | Status   |
      | t1 | Ticket 1 | resolved |
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "fields": {
    "~f1~": ""
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_{f1}.errors[0].code" should be equal to "required"
