@new
Feature: /ticket_forms
  I want to check built-in fields

  Background:
    Given I'm authenticated as admin
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |

  Scenario Outline: I check built-in custom fields
    Given the setting "core.use_<setting_name>" is set to 1
    And the only default ticket layout exists with fields:
      | agent_layout |
      | <field_name> |
    And only the following <entity_type> records exist:
      | #  | Title   |
      | p1 | Title 1 |
      | p2 | Title 2 |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "<field_name>": ~p2~
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200
    And the JSON node "data.<field_name>" should be equal to "{p2}"

    Examples:
      | entity_type    | field_name | setting_name    |
      | Product        | product    | product         |
      | TicketCategory | category   | ticket_category |
      | TicketWorkflow | workflow   | ticket_workflow |
      | TicketPriority | priority   | ticket_priority |
