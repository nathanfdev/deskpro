@new
Feature: /ticket_forms endpoint
  I want to check layout with field criteria based on Category

  Background:
    Given I'm authenticated as admin
    And I have only default brand
    And a user with "user_1@deskpro.dev" email exists
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
    And the setting "core.use_ticket_category" is set to 1
    And only the following TicketCategory records exist:
      | #  | Title   |
      | p1 | Title 1 |
      | p2 | Title 2 |
    And the only default ticket layout exists with fields:
      | agent_layout | agent_layout_options                                                                                                                           |
      | category     |                                                                                                                                                |
      | cc           | {"on_newticket": true, "criteria":{"version":1,"mode":"all","terms":[{"type":"CheckCategory","op":"is","options":{"category_ids":["~p1~"]}}]}} |

  Scenario: I check that field is hidden if no product defined
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "cc": ["user_1@deskpro.dev"]
}
    """
    Then the JSON node "errors.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.errors[0].message" should be equal to "Unexpected field names: cc"

  Scenario: I check that field is hidden if not required product
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "category": ~p2~,
  "cc": ["user_1@deskpro.dev"]
}
    """
    Then the JSON node "errors.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.errors[0].message" should be equal to "Unexpected field names: cc"

  Scenario: I check that field is present on the form
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "category": ~p1~,
  "cc": ["user_1@deskpro.dev"]
}
    """
    Then the JSON node "errors.errors[0].code" should not exist
