@new
Feature: /ticket_forms endpoint
  Create a layout with FieldA, criteria based on Category1 being selected.
  FieldA is required. Make sure the required option is applied. i.e. if not provided a form error.

  Background:
    Given I'm authenticated as admin
    And I have only default brand
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
    And the setting "core.use_ticket_category" is set to 1
    And only the following TicketCategory records exist:
      | #  | Title   |
      | p1 | Title 1 |
      | p2 | Title 2 |
    And only the following custom ticket fields exist:
      | #  | Type | Title        | Options                  |
      | f1 | text | Text field 1 | {"agent_required": true} |
    And the only default ticket layout exists with fields:
      | agent_layout      | agent_layout_options                                                                                                                           |
      | category          |                                                                                                                                                |
      | ticket_field_{f1} | {"on_newticket": true, "criteria":{"version":1,"mode":"all","terms":[{"type":"CheckCategory","op":"is","options":{"category_ids":["~p1~"]}}]}} |

  Scenario: I check that validation is applied on criteria field
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "category": ~p1~,
  "fields": {
    "~f1~": ""
  }
}
    """
    Then the JSON node "errors.fields.fields.fields.fields_{f1}.errors[0].code" should be equal to "required"
