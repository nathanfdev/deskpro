@new @custom-fields
Feature: Edit ticket form custom fields

  Background:
    Given I'm authenticated as user
    And I have only default brand
    And user has defaultBrand brand
    And the "{defaultBrand}" setting "core_tickets.use_ref" is set to "1"
    And I set permission "tickets.use" = 1 for "registered" usergroup
    And the following languages are enabled:
      | default |
    And default everyone user group exists
    And only the following Department records exist:
      | #  | Parent | Title          | Brands           | Is Tickets Enabled |
      | d1 | NULL   | Department 1   | [{defaultBrand}] | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup everyone
    And only the following Ticket records exist:
      | #        | Person | Ref | Department | Subject        | Date Last User Reply |
      | ticket_1 | {user} | ref | {d1}       | Ticket Subject | 2015-06-15 17:00:00  |
    And only the following TicketMessage records exist:
      | Ticket     | Person | Message |
      | {ticket_1} | {user} | text    |

  Scenario Outline: I check saving text/textarea fields
    Given only the following custom ticket fields exist:
      | # | Type   | Title      |
      | t | <type> | Text field |
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |
    And I go to "/tickets/ref/edit"

    When I fill in "ticket[ticket_field_{t}][data]" with "12345"
    And I press "Save"
    Then I should be on "/tickets/ref"

    When I go to "/tickets/ref/edit"
    Then the "ticket[ticket_field_{t}][data]" field should contain "12345"

    Examples:
      | type     |
      | text     |
      | textarea |

  Scenario: I check saving toggle on field
    Given only the following custom ticket fields exist:
      | # | Type   | Title      |
      | t | toggle | Text field |
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |
    And I go to "/tickets/ref/edit"

    When I check "ticket[ticket_field_{t}][data]"
    And I press "Save"
    Then I should be on "/tickets/ref"

    When I go to "/tickets/ref/edit"
    Then the "ticket[ticket_field_{t}][data]" checkbox should be checked

    When I uncheck "ticket[ticket_field_{t}][data]"
    And I press "Save"
    Then I should be on "/tickets/ref"

    When I go to "/tickets/ref/edit"
    Then the "ticket[ticket_field_{t}][data]" checkbox should not be checked

  Scenario Outline: I check saving single selectbox field
    Given only the following custom ticket fields exist:
      | #  | Type          | Title        | Parent |
      | t  | single_choice | Custom field |        |
      | c1 |               | Choice 1     | {t}    |
      | c2 |               | Choice 2     | {t}    |
      | c3 |               | Choice 3     | {t}    |
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |
    And I go to "/tickets/ref/edit"

    When I select "<value>" from "ticket[ticket_field_{t}][data]"
    And I press "Save"
    Then I should be on "/tickets/ref"

    When I go to "/tickets/ref/edit"
    Then the "ticket[ticket_field_{t}][data]" field should contain "<value>"

    Examples:
      | value |
      | {c1}  |
      | {c2}  |
      | {c3}  |

  Scenario: I check saving checkbox group
    Given only the following custom ticket fields exist:
      | #  | Type           | Title        | Parent |
      | t  | checkbox_group | Custom field |        |
      | c1 |                | Choice 1     | {t}    |
      | c2 |                | Choice 2     | {t}    |
      | c3 |                | Choice 3     | {t}    |
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |
    And I go to "/tickets/ref/edit"

    When I check "ticket_ticket_field_{t}_data_0"
    When I check "ticket_ticket_field_{t}_data_2"
    And I press "Save"
    Then I should be on "/tickets/ref"

    When I go to "/tickets/ref/edit"
    Then the "ticket_ticket_field_{t}_data_0" checkbox should be checked
    And the "ticket_ticket_field_{t}_data_1" checkbox should not be checked
    And the "ticket_ticket_field_{t}_data_2" checkbox should be checked

  Scenario Outline: I check saving radio group
    Given only the following custom ticket fields exist:
      | #  | Type        | Title        | Parent |
      | t  | radio_group | Custom field |        |
      | c1 |             | Choice 1     | {t}    |
      | c2 |             | Choice 2     | {t}    |
      | c3 |             | Choice 3     | {t}    |
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |
    And I go to "/tickets/ref/edit"

    When I fill in "ticket[ticket_field_{t}][data]" with "<value>"
    And I press "Save"
    Then I should be on "/tickets/ref"

    When I go to "/tickets/ref/edit"
    Then the "ticket[ticket_field_{t}][data]" field should contain "<value>"

    Examples:
      | value |
      | {c1}  |
      | {c2}  |
      | {c3}  |

  Scenario: I check saving multi selectbox default value
    Given only the following custom ticket fields exist:
      | #  | Type         | Title        | Parent |
      | t  | multi_choice | Custom field |        |
      | c1 |              | Choice 1     | {t}    |
      | c2 |              | Choice 2     | {t}    |
      | c3 |              | Choice 3     | {t}    |
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |
    And I go to "/tickets/ref/edit"

    When I select "{c1}" from "ticket[ticket_field_{t}][data][]"
    And I additionally select "{c3}" from "ticket[ticket_field_{t}][data][]"
    And I press "Save"
    Then I should be on "/tickets/ref"

    When I go to "/tickets/ref/edit"
    Then the "ticket[ticket_field_{t}][data][]" multiple field should contain "{c1},{c3}"

  Scenario Outline: I check saving date field
    Given only the following custom ticket fields exist:
      | # | Type | Title      |
      | t | date | Date field |
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |
    And I go to "/tickets/ref/edit"

    When I select "<year>" from "ticket[ticket_field_{t}][data][year]"
    And I select "6" from "ticket[ticket_field_{t}][data][month]"
    And I select "15" from "ticket[ticket_field_{t}][data][day]"
    And I press "Save"
    Then I should be on "/tickets/ref"

    When I go to "/tickets/ref/edit"
    Then the "ticket[ticket_field_{t}][data][year]" field should contain "<year>"
    And the "ticket[ticket_field_{t}][data][month]" field should contain "6"
    And the "ticket[ticket_field_{t}][data][day]" field should contain "15"

    Examples:
      | year |
      | 2016 |
      | 1951 |

  Scenario Outline: I check saving datetime field
    Given only the following custom ticket fields exist:
      | # | Type     | Title      |
      | t | datetime | Date field |
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |
    And I go to "/tickets/ref/edit"

    When I select "<year>" from "ticket[ticket_field_{t}][data][date][year]"
    And I select "6" from "ticket[ticket_field_{t}][data][date][month]"
    And I select "15" from "ticket[ticket_field_{t}][data][date][day]"
    And I select "20" from "ticket[ticket_field_{t}][data][time][hour]"
    And I select "30" from "ticket[ticket_field_{t}][data][time][minute]"
    And I press "Save"
    Then I should be on "/tickets/ref"

    When I go to "/tickets/ref/edit"
    Then the "ticket[ticket_field_{t}][data][date][year]" field should contain "<year>"
    And the "ticket[ticket_field_{t}][data][date][month]" field should contain "6"
    And the "ticket[ticket_field_{t}][data][date][day]" field should contain "15"
    And the "ticket[ticket_field_{t}][data][time][hour]" field should contain "20"
    And the "ticket[ticket_field_{t}][data][time][minute]" field should contain "30"

    Examples:
      | year |
      | 2016 |
      | 1951 |
