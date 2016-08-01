@new @custom-fields
Feature: Custom per field types

  Background:
    Given I'm authenticated as user
    And only the following Department records exist:
      | #  | Title        | Is Tickets Enabled |
      | d1 | Department 1 | 1                  |
      | d2 | Department 2 | 1                  |
    And only the following Ticket records exist:
      | #        | Person | Department | Ref | Subject        | Date Last User Reply |
      | ticket_1 | {user} | {d1}       | ref | Ticket Subject | 2015-06-15 17:00:00  |
    And only the following TicketMessage records exist:
      | Ticket     | Person | Message |
      | {ticket_1} | {user} | text    |

  Scenario Outline: I update select/radio custom per user field
    Given only the following custom per user fields exist:
      | #  | Parent | Title    | Context | Type   |
      | f1 |        | Field 1  |         | <type> |
      | c1 | {f1}   | Choice 1 | {user}  |        |
      | c2 | {f1}   | Choice 2 | {user}  |        |
    And the only default ticket layout exists with fields:
      | user_layout       |
      | custom_field_{f1} |

    When I go to "/tickets/ref/edit"
    And I fill in "ticket[custom_field_{f1}][data]" with "{c1}"
    And I press "Save"
    And I go to "/tickets/ref/edit"
    Then the "ticket[custom_field_{f1}][data]" field should contain "{c1}"

    Examples:
      | type          |
      | single_choice |
      | radio_group   |

  Scenario: I update multi-select custom per user field
    Given only the following custom per user fields exist:
      | #  | Parent | Title    | Context | Type         |
      | f1 |        | Field 1  |         | multi_choice |
      | c1 | {f1}   | Choice 1 | {user}  |              |
      | c2 | {f1}   | Choice 2 | {user}  |              |
    And the only default ticket layout exists with fields:
      | user_layout       |
      | custom_field_{f1} |

    When I go to "/tickets/ref/edit"
    And I select "Choice 1" from "ticket[custom_field_{f1}][data][]"
    And I press "Save"
    And I go to "/tickets/ref/edit"
    Then the "ticket[custom_field_{f1}][data][]" multiple field should contain "{c1}"

  Scenario: I update checkbox group custom per user field
    Given only the following custom per user fields exist:
      | #  | Parent | Title    | Context | Type           |
      | f1 |        | Field 1  |         | checkbox_group |
      | c1 | {f1}   | Choice 1 | {user}  |                |
      | c2 | {f1}   | Choice 2 | {user}  |                |
    And the only default ticket layout exists with fields:
      | user_layout       |
      | custom_field_{f1} |

    When I go to "/tickets/ref/edit"
    And I check "ticket_custom_field_{f1}_data_{c1}"
    And I check "ticket_custom_field_{f1}_data_{c2}"
    And I press "Save"
    And I go to "/tickets/ref/edit"
    Then the "ticket_custom_field_{f1}_data_{c1}" checkbox should be checked
    And the "ticket_custom_field_{f1}_data_{c2}" checkbox should be checked
