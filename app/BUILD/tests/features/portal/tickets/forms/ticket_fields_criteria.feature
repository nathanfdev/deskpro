@new
Feature: New ticket form
  I want to check fields criteria

  Background:
    Given I have only default brand
    And I'm authenticated as user
    And user has defaultBrand brand
    And the "{defaultBrand}" setting "core_tickets.use_ref" is set to "1"
    And a user with "user_1@deskpro.dev" email exists
    And only the following Department records exist:
      | #  | Title        | Is Tickets Enabled |
      | d1 | Department 1 | 1                  |
      | d2 | Department 2 | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup registered
    And I grant the "{d2}" department permission of tickets app for usergroup registered
    And only the following Ticket records exist:
      | #        | Person | Department | Ref | Subject        | Date Last User Reply |
      | ticket_1 | {user} | {d2}       | ref | Ticket Subject | 2015-06-15 17:00:00  |
    And only the following TicketMessage records exist:
      | Ticket     | Person | Message |
      | {ticket_1} | {user} | text    |

  Scenario: I check that field is not on the form on page load
    Given the only default ticket layout exists with fields:
      | user_layout | user_layout_options                                                                                                                                 |
      | cc          | {"on_editticket": true, "criteria":{"version":1,"mode":"all","terms":[{"type":"CheckDepartment","op":"is","options":{"department_ids":["~d1~"]}}]}} |
    When I go to "/tickets/ref/edit"
    Then I should see ".form-ticket" form fields in following order:
      | name               |
      | ticket[department] |
      | ticket[subject]    |

  Scenario: I check that field is on the form on page load
    Given the only default ticket layout exists with fields:
      | user_layout | user_layout_options                                                                                                                                 |
      | cc          | {"on_editticket": true, "criteria":{"version":1,"mode":"all","terms":[{"type":"CheckDepartment","op":"is","options":{"department_ids":["~d2~"]}}]}} |

    When I go to "/tickets/ref/edit"
    Then I should see ".form-ticket" form fields in following order:
      | name               |
      | ticket[cc]         |
      | ticket[department] |
      | ticket[subject]    |

  Scenario: I check that department field is on the form even it doesn't match criteria
    Given the only default ticket layout exists with fields:
      | user_layout | user_layout_options                                                                                                                                 |
      | department  | {"on_editticket": true, "criteria":{"version":1,"mode":"all","terms":[{"type":"CheckDepartment","op":"is","options":{"department_ids":["~d1~"]}}]}} |

    When I go to "/tickets/ref/edit"
    Then I should see ".form-ticket" form fields in following order:
      | name               |
      | ticket[department] |
      | ticket[subject]    |

  Scenario Outline: I check layout with field criteria based on built-in field
    Given the setting "core.use_<setting_name>" is set to 1
    And only the following <entity_type> records exist:
      | #  | Title   |
      | p1 | Title 1 |
      | p2 | Title 2 |
    And the only default ticket layout exists with fields:
      | user_layout | user_layout_options                                                                                                                           |
      | cc          | {"on_editticket": true, "criteria":{"version":1,"mode":"all","terms":[{"type":"<criteria_type>","op":"is","options":{"<field_name>_ids":["~p1~"]}}]}} |

    When the "{ticket_1}" record "<field_name>" prop is equal to "{p1}"
    And I go to "/tickets/ref/edit"
    Then I should see ".form-ticket" form fields in following order:
      | name               |
      | ticket[cc]         |
      | ticket[department] |

    When the "{ticket_1}" record "<field_name>" prop is equal to "{p2}"
    And I go to "/tickets/ref/edit"
    Then I should see ".form-ticket" form fields in following order:
      | name               |
      | ticket[department] |
      | ticket[subject]    |

    Examples:
      | entity_type    | field_name | setting_name    | criteria_type |
      | Product        | product    | product         | CheckProduct  |
      | TicketCategory | category   | ticket_category | CheckCategory |
      | TicketPriority | priority   | ticket_priority | CheckPriority |
