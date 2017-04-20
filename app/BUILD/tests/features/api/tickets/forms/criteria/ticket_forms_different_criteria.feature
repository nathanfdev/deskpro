@new
Feature: /ticket_forms endpoint
  Default layout and DepA layout both have FieldA, but DepA layout has a criteria on FieldA (e.g. Category is 1)
  Make sure criteria is applied properly only in DepA

  Background:
    Given I'm authenticated as admin
    And I have only default brand
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
      | d2 | Department 1 | [{defaultBrand}] | 1                  |
    And the setting "core.use_ticket_category" is set to 1
    And only the following TicketCategory records exist:
      | #  | Title   |
      | p1 | Title 1 |
      | p2 | Title 2 |
    And only the following custom ticket fields exist:
      | #  | Type | Title      |
      | f1 | text | Text field |
      | f2 | text | Text field |
    And the only default ticket layout exists with fields:
      | agent_layout      | agent_layout_options                                                                                                                           |
      | category          |                                                                                                                                                |
      | ticket_field_{f1} | {"on_newticket": true, "criteria":{"version":1,"mode":"all","terms":[{"type":"CheckCategory","op":"is","options":{"category_ids":["~p1~"]}}]}} |
      | ticket_field_{f2} |                                                                                                                                                |
    And the ticket layout exists for "{d1}" department with fields:
      | agent_layout      |
      | category          |
      | ticket_field_{f1} |
      | ticket_field_{f2} |

  Scenario: I check criteria is applied on layout change
    Given only the following Ticket records exist:
      | #  | Subject | Department |
      | t1 | Ticket  | {d2}       |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "fields": {
    "~f1~": "val 1"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.fields.fields.errors[0].message" should be equal to "Unexpected field names: ~f1~"

  Scenario: I check criteria is not applied on layout change
    Given only the following Ticket records exist:
      | #  | Subject | Department |
      | t1 | Ticket  | {d1}       |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "fields": {
    "~f1~": "val 1"
  }
}
    """
    Then the response status code should be 204
