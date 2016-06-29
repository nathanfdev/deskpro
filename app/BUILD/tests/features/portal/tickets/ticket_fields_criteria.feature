@new
Feature: New ticket form
  I want to check fields criteria

  Background:
    Given I'm authenticated as user
    And a user with "user_1@deskpro.dev" email exists
    And only the following Department records exist:
      | #  | Parent | Title        | Is Tickets Enabled |
      | d1 |        | Department 1 | 1                  |
      | d2 |        | Department 2 | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup registered
    And I grant the "{d2}" department permission of tickets app for usergroup registered
    And only the following Ticket records exist:
      | #        | Person | Department | Ref | Subject        | Date Last User Reply |
      | ticket_1 | {user} | {d2}       | ref | Ticket Subject | 2015-06-15 17:00:00  |
    And only the following TicketMessage records exist:
      | Ticket     | Person | Message |
      | {ticket_1} | {user} | text    |

  Scenario: I check that field is not on form on page load
    Given the only default ticket layout exists with fields:
      | user_layout | user_layout_options                                                                                                                                 |
      | cc          | {"on_editticket": true, "criteria":{"version":1,"mode":"all","terms":[{"type":"CheckDepartment","op":"is","options":{"department_ids":["~d1~"]}}]}} |
    When I go to "/tickets/ref/edit"
    Then I should see ".form-ticket" form fields in following order:
      | name               |
      | ticket[department] |
      | ticket[subject]    |

  Scenario: I check that field is on form on page load
    Given the only default ticket layout exists with fields:
      | user_layout | user_layout_options                                                                                                                                 |
      | cc          | {"on_editticket": true, "criteria":{"version":1,"mode":"all","terms":[{"type":"CheckDepartment","op":"is","options":{"department_ids":["~d2~"]}}]}} |

    When I go to "/tickets/ref/edit"
    Then I should see ".form-ticket" form fields in following order:
      | name               |
      | ticket[cc]         |
      | ticket[department] |
      | ticket[subject]    |

  Scenario: I check that department fields is on the form even it doesn't match criteria
    Given the only default ticket layout exists with fields:
      | user_layout | user_layout_options                                                                                                                                 |
      | department  | {"on_editticket": true, "criteria":{"version":1,"mode":"all","terms":[{"type":"CheckDepartment","op":"is","options":{"department_ids":["~d1~"]}}]}} |

    When I go to "/tickets/ref/edit"
    Then I should see ".form-ticket" form fields in following order:
      | name               |
      | ticket[department] |
      | ticket[subject]    |
