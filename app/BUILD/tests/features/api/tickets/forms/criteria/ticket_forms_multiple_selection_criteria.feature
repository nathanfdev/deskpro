@new
Feature: /ticket_forms endpoint
  Layout with field criteria with multiple selections (e.g. Category [1, 2])

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
      | p3 | Title 3 |
    And the only default ticket layout exists with fields:
      | agent_layout | agent_layout_options                                                                                                                                   |
      | category     |                                                                                                                                                        |
      | cc           | {"on_newticket": true, "criteria":{"version":1,"mode":"all","terms":[{"type":"CheckCategory","op":"is","options":{"category_ids":["~p1~", "~p2~"]}}]}} |

  Scenario Outline: I check category match
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "category": <category>,
  "cc": ["user_1@deskpro.dev"]
}
    """
    Then the JSON node "errors.errors[0].code" should not exist

    Examples:
      | category |
      | ~p1~     |
      | ~p2~     |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "category": ~p3~,
  "cc": ["user_1@deskpro.dev"]
}
    """
    Then the JSON node "errors.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.errors[0].message" should be equal to "Unexpected field names: cc"
