@new
Feature: /ticket_forms endpoint
  I want to check layout criteria mode

  Background:
    Given I'm authenticated as admin
    And I have only default brand
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
    And only the following custom ticket fields exist:
      | #  | Type | Title        |
      | f1 | text | Text field 1 |
      | f2 | text | Text field 2 |
      | f3 | text | Text field 3 |
    And the only default ticket layout exists with fields:
      | agent_layout      | agent_layout_options                                                                                                                                                                                                                                                                                        |
      | ticket_field_{f1} |                                                                                                                                                                                                                                                                                                             |
      | ticket_field_{f2} |                                                                                                                                                                                                                                                                                                             |
      | ticket_field_{f3} | {"on_newticket": true, "criteria":{"version":1,"mode":"any","terms":[{"type":"CheckTicketField~f1~","op":"contains","options":{"value": "val 1", "type_name":"text","field_id":"~f1~"}}, {"type":"CheckTicketField~f2~","op":"contains","options":{"value": "val 2","type_name":"text","field_id":"~f2~"}}]}} |

  Scenario: I check that if no matches then the field with criteria is not present on the form
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "fields": {
    "~f3~": "val 3"
  }
}
    """
    Then the JSON node "errors.fields.fields.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.fields.fields.errors[0].message" should contain "~f3~"

  Scenario: I check that if just one match then the field with criteria is not present on the form
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "fields": {
    "~f1~": "val 1",
    "~f3~": "val 3"
  }
}
    """
    Then the JSON node "errors.fields.fields" should not exist

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "fields": {
    "~f2~": "val 2",
    "~f3~": "val 3"
  }
}
    """
    Then the JSON node "errors.fields.fields" should not exist

  Scenario: I check if all matches the the fields is on the form
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "fields": {
    "~f1~": "val 1",
    "~f2~": "val 2",
    "~f3~": "val 3"
  }
}
    """
    Then the JSON node "errors.fields.fields" should not exist
