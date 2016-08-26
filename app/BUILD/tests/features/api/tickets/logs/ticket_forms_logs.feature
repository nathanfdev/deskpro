@new
Feature: Ticket logs
  I want to check /ticket_forms logs

  Background:
    Given no Person records exist
    And I'm authenticated as admin
    And I have only default brand
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
      | d2 | Department 2 | [{defaultBrand}] | 1                  |
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |

  Scenario: I check created logs
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "subject": "Sample Ticket",
  "department": ~d1~,
  "message": {
    "message": "ticket message"
  }
}
    """
    Then the response status code should be 201
    And the "{lastCreatedId}" ticket should have "ticket_created" log
    And the "{lastCreatedId}" ticket should have "message_created" log

  Scenario: I check changed subject log
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "subject": "Sample Ticket"
}
    """
    Then the response status code should be 204
    And the "{t1}" ticket should have "changed_subject" log

  Scenario: I check changed department log
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "department": ~d1~
}
    """
    Then the response status code should be 204
    And the "{t1}" ticket should have "changed_department" log

  Scenario: I check changed person log
    Given "user_1@deskpro.dev" user exists
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "person": ~user_1@deskpro.dev~
}
    """
    Then the response status code should be 204
    And the "{t1}" ticket should have "changed_person" log

  Scenario: I check changed_labels log
    Given "user_1@deskpro.dev" user exists
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "labels": ["label1", "label2"]
}
    """
    Then the response status code should be 204
    And the "{t1}" ticket should have "changed_labels" log

  Scenario Outline: I check changed built-in custom field logs
    Given the setting "core.use_<setting_name>" is set to 1
    And only the following <entity> records exist:
      | #  | Title   |
      | p1 | Title 1 |
      | p2 | Title 2 |
    And the only default ticket layout exists with fields:
      | agent_layout |
      | <type>       |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "<type>": ~p1~
}
    """
    Then the response status code should be 204
    And the "{t1}" ticket should have "changed_<type>" log

    Examples:
      | type     | entity         | setting_name    |
      | product  | Product        | product         |
      | priority | TicketPriority | ticket_priority |
      | category | TicketCategory | ticket_category |
      | workflow | TicketWorkflow | ticket_workflow |

  Scenario Outline: I check participants logs
    Given "person@deskpro.dev" <role> exists
    And the only default ticket layout exists with fields:
      | agent_layout |
      | <type>       |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "<type>": ["person@deskpro.dev"]
}
    """
    Then the response status code should be 204
    And the "{t1}" ticket should have "changed_<role>_participants" log

    Examples:
      | type      | role  |
      | cc        | user  |
      | followers | agent |

  Scenario: I check changed_custom_field log
    Given only the following custom ticket fields exist:
      | #          | Type | Title      |
      | text_field | text | Text field |
    And the only default ticket layout exists with fields:
      | agent_layout              |
      | ticket_field_{text_field} |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "fields": {
    "~text_field~": "some value"
  }
}
    """
    Then the response status code should be 204
    And the "{t1}" ticket should have "changed_custom_field" log
