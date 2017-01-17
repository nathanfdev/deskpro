@new
Feature: New ticket form validation
  I want to check built in fields validation

  Background:
    Given I'm authenticated as user

  Scenario Outline: I check that there is no field because it is disabled
    Given  the setting "core.use_<setting_name>" is set to 0
    And only the following <entity_type> records exist:
      | #  | Title   |
      | p1 | Title 1 |
      | p2 | Title 2 |

    When I go to "/new-ticket"
    Then I should not see the "ticket[<field_name>]" field

    Examples:
      | entity_type    | field_name | setting_name    |
      | Product        | product    | product         |
      | TicketCategory | category   | ticket_category |
      | TicketPriority | priority   | ticket_priority |

  Scenario Outline: I check that there is no field because no choices
    Given the setting "core.use_<setting_name>" is set to 1
    And the only default ticket layout exists with fields:
      | agent_layout |
      | <field_name> |
    And no <entity_type> records exist

    When I go to "/new-ticket"
    Then I should not see the "ticket[<field_name>]" field

    Examples:
      | entity_type    | field_name | setting_name    |
      | Product        | product    | product         |
      | TicketCategory | category   | ticket_category |
      | TicketPriority | priority   | ticket_priority |

  Scenario Outline: I check required common field validation
    Given  the setting "core.<use_setting_name>" is set to 1
    And only the following <entity_type> records exist:
      | #  | Title   |
      | p1 | Title 1 |
      | p2 | Title 2 |
    And the only default ticket layout exists with fields:
      | user_layout  |
      | <field_name> |

    When the setting "core_tickets.field_validation_ticket_<validation_setting_name>_user_required" is set to 0
    And I go to "/new-ticket"
    And I press "Submit"
    And "ticket[<field_name>]" form field should have 0 errors

    When the setting "core_tickets.field_validation_ticket_<validation_setting_name>_user_required" is set to 1
    And I go to "/new-ticket"
    And I press "Submit"
    Then "ticket[<field_name>]" form field should have error with the phrase "portal.forms.error_required"
    And "ticket[<field_name>]" form field should have 1 error

    Examples:
      | entity_type    | field_name | use_setting_name    | validation_setting_name |
      | Product        | product    | use_product         | prod                    |
      | TicketPriority | priority   | use_ticket_priority | pri                     |
      | TicketCategory | category   | use_ticket_category | cat                     |

  Scenario Outline: I check leaf node validation
    Given  the setting "core.<use_setting_name>" is set to 1
    And only the following <entity_type> records exist:
      | #  | Parent | Title   |
      | p1 | NULL   | Title 1 |
      | p2 | {p1}   | Title 2 |
      | p3 | {p1}   | Title 3 |
    And the only default ticket layout exists with fields:
      | user_layout  |
      | <field_name> |

    When I go to "/new-ticket"
    And I fill in "ticket[<field_name>]" with "{p1}"
    And I press "Submit"

    Then "ticket[<field_name>]" form field should have error with the phrase "portal.forms.error_<error_code>"
    And "ticket[<field_name>]" form field should have 1 errors

    Examples:
      | entity_type    | field_name | use_setting_name    | error_code              |
      | Product        | product    | use_product         | not_assignable_product  |
      | TicketCategory | category   | use_ticket_category | not_assignable_category |
