@new @custom-fields
Feature: Custom per organization fields

  Background:
    Given I'm authenticated as user
    And only the following Department records exist:
      | #  | Title        | Is Tickets Enabled |
      | d1 | Department 1 | 1                  |
      | d2 | Department 2 | 1                  |
    And only the following Organization records exist:
      | #  | Name           |
      | o1 | Organization 1 |
    And only the following Ticket records exist:
      | #        | Person | Department | Organization | Ref | Subject        | Date Last User Reply |
      | ticket_1 | {user} | {d1}       | {o1}         | ref | Ticket Subject | 2015-06-15 17:00:00  |
    And only the following TicketMessage records exist:
      | Ticket     | Person | Message |
      | {ticket_1} | {user} | text    |

  Scenario: I check the custom per organization fields are on the form
    Given only the following custom per organization fields exist:
      | #  | Parent | Title    | Context |
      | f1 |        | Field 1  |         |
      | c1 | {f1}   | Choice 1 | {o1}    |
      | c2 | {f1}   | Choice 2 | {o1}    |
      | f2 |        | Field 2  |         |
      | c3 | {f2}   | Choice 3 | {o1}    |
      | c4 | {f2}   | Choice 4 | {o1}    |
    And the only default ticket layout exists with fields:
      | user_layout       |
      | custom_field_{f1} |
      | custom_field_{f2} |
    And the "{user}" record organization prop is equal to "{o1}"

    When I go to "/new-ticket"
    Then I should see ".form-ticket" form fields in following order:
      | name                            |
      | ticket[custom_field_{f1}][data] |
      | ticket[custom_field_{f2}][data] |

  Scenario: I edit ticket with custom per organization fields
    Given only the following custom per organization fields exist:
      | #  | Parent | Title    | Context |
      | f1 |        | Field 1  |         |
      | c1 | {f1}   | Choice 1 | {o1}    |
      | c2 | {f1}   | Choice 2 | {o1}    |
    And the only default ticket layout exists with fields:
      | user_layout       |
      | custom_field_{f1} |
    And the "{user}" record organization prop is equal to "{o1}"

    When I go to "/tickets/ref/edit"
    And I select "Choice 1" from "ticket[custom_field_{f1}][data]"
    And I press "Save"
    And I go to "/tickets/ref/edit"
    Then the "ticket[custom_field_{f1}][data]" field should contain "{c1}"

  Scenario: I don't see custom per organization field because the user doesn't belong to the tickets organization
    Given only the following custom per organization fields exist:
      | #  | Parent | Title    | Context |
      | f1 |        | Field 1  |         |
      | c1 | {f1}   | Choice 1 | {o1}    |
      | c2 | {f1}   | Choice 2 | {o1}    |
    And the only default ticket layout exists with fields:
      | user_layout       |
      | custom_field_{f1} |

    When I go to "/new-ticket"
    Then I should see ".form-ticket" form fields in following order:
      | name               |
      | ticket[department] |

  Scenario: I don't see custom per organization field because it has no choices
    Given only the following custom per user fields exist:
      | #  | Parent | Title    | Context |
      | f1 |        | Field 1  |         |
    And the only default ticket layout exists with fields:
      | user_layout       |
      | custom_field_{f1} |
    And the "{user}" record organization prop is equal to "{o1}"

    When I go to "/new-ticket"
    Then I should see ".form-ticket" form fields in following order:
      | name               |
      | ticket[department] |

  Scenario: I don't see custom per organization field because the ticket organization has no its choices
    Given the following Organization records exist:
      | #  | Name           |
      | o2 | Organization 2 |
    Given only the following custom per user fields exist:
      | #  | Parent | Title    | Context |
      | f1 |        | Field 1  |         |
      | c1 | {f1}   | Choice 1 | {o2}    |
      | c2 | {f1}   | Choice 2 | {o2}    |
    And the only default ticket layout exists with fields:
      | user_layout       |
      | custom_field_{f1} |
    And the "{user}" record organization prop is equal to "{o1}"

    When I go to "/new-ticket"
    Then I should see ".form-ticket" form fields in following order:
      | name               |
      | ticket[department] |
