@new
Feature: Check reply to ticket
  I want to check reply for ticket is sent and rejected if ticket was closed/archived

  Background:
    Given I'm authenticated as user
    And I have only default brand
    And user has defaultBrand brand
    And I disable anti-abuse rate limiting
    And I set permission "tickets.use" = 1 for registered usergroup
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup registered

  Scenario: I check successful reply
    Given only the following Ticket records exist:
      | #        | Person | Department | Brand          | Ref | Subject        | Status        | Date Last User Reply | Message              |
      | ticket_1 | {user} | {d1}       | {defaultBrand} | ref | Ticket Subject | awaiting_user | 2016-01-01 10:00:00  | Ticket message text. |

    When I go to "/tickets/ref"
    Then I fill in "ticket_reply[ticket_message][message]" with "Ticket message text."
    And I press "Reply"
    Then the response should not contain "Ticket should be neither resolved nor archived."

  Scenario: I check no reply box for closed tickets
    Given only the following Ticket records exist:
      | #        | Person | Department | Brand          | Ref | Subject        | Status   | Date Archived       | Date Last User Reply | Message              |
      | ticket_1 | {user} | {d1}       | {defaultBrand} | ref | Ticket Subject | archived | 2016-01-01 12:00:00 | 2016-01-01 10:00:00  | Ticket message text. |

    When I go to "/tickets/ref"
    Then the response should contain "Ticket Subject"
    And I should not see an "ticket_reply[ticket_message][message]" element
