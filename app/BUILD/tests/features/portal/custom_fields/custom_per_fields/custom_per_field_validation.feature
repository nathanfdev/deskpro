@new @custom-fields
Feature: Custom per fields validation

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

  Scenario: I try to skip required custom per user field
    Given only the following custom per user fields exist:
      | #  | Parent | Title    | Context | Options            | Type         |
      | f1 |        | Field 1  |         | {"required": true} | multi_choice |
      | c1 | {f1}   | Choice 1 | {user}  |                    |              |
      | c2 | {f1}   | Choice 2 | {user}  |                    |              |
    And the only default ticket layout exists with fields:
      | user_layout       |
      | custom_field_{f1} |

    When I go to "/tickets/ref/edit"
    And I press "Save"

    Then "ticket[custom_field_{f1}][data][]" form field should have error with the phrase "portal.forms.error_required"
    And "ticket[custom_field_{f1}][data][]" form field should have 1 error

  Scenario: I skip not required custom per user field
    Given only the following custom per user fields exist:
      | #  | Parent | Title    | Context | Type         |
      | f1 |        | Field 1  |         | multi_choice |
      | c1 | {f1}   | Choice 1 | {user}  |              |
      | c2 | {f1}   | Choice 2 | {user}  |              |
    And the only default ticket layout exists with fields:
      | user_layout       |
      | custom_field_{f1} |

    When I go to "/tickets/ref/edit"
    And I press "Save"
    Then I should be on "/tickets/ref"
