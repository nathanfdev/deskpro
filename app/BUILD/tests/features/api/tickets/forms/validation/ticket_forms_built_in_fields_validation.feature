@new
Feature: /ticket_forms validation
  I want to check built in fields validation

  Background:
    Given I'm authenticated as admin

  Scenario Outline: I sent unknown choice:
    Given the setting "core.use_<setting_name>" is set to 1
    And the only default ticket layout exists with fields:
      | agent_layout |
      | <field_name> |
    And only the following <entity_type> records exist:
      | #  | Title   |
      | p1 | Title 1 |
      | p2 | Title 2 |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "<field_name>": 404
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.<field_name>.errors" should have 1 element
    And the JSON node "errors.fields.<field_name>.errors[0].code" should be equal to "bad_choice"

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "<field_name>": {
    "id": 1
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.<field_name>.errors" should have 1 element
    And the JSON node "errors.fields.<field_name>.errors[0].code" should be equal to "invalid_data_type"

    Examples:
      | entity_type    | field_name | setting_name    |
      | Product        | product    | product         |
      | TicketCategory | category   | ticket_category |
      | TicketWorkflow | workflow   | ticket_workflow |
      | TicketPriority | priority   | ticket_priority |

  Scenario Outline: I check that there is no field because it is disabled
    Given the setting "core.use_<setting_name>" is set to 0
    And the only default ticket layout exists with fields:
      | agent_layout |
      | <field_name> |
    And only the following <entity_type> records exist:
      | #  | Title   |
      | p1 | Title 1 |
      | p2 | Title 2 |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "<field_name>": ~p1~
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors" should have 1 element
    And the JSON node "errors.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.errors[0].message" should contain "<field_name>"

    Examples:
      | entity_type    | field_name | setting_name    |
      | Product        | product    | product         |
      | TicketCategory | category   | ticket_category |
      | TicketWorkflow | workflow   | ticket_workflow |
      | TicketPriority | priority   | ticket_priority |

  Scenario Outline: I check that there is no field because no choices
    Given the setting "core.use_<setting_name>" is set to 1
    And the only default ticket layout exists with fields:
      | agent_layout |
      | <field_name> |
    And no <entity_type> records exist

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "<field_name>": 0
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors" should have 1 element
    And the JSON node "errors.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.errors[0].message" should contain "<field_name>"

    Examples:
      | entity_type    | field_name | setting_name    |
      | Product        | product    | product         |
      | TicketCategory | category   | ticket_category |
      | TicketWorkflow | workflow   | ticket_workflow |
      | TicketPriority | priority   | ticket_priority |
