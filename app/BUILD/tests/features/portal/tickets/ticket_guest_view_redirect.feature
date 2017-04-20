@new
Feature: Ticket guest view redirect to /register page

  Background:
    Given no Person records exist
    And I have only default brand
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme
    And I disable anti-abuse rate limiting
    And I set permission "tickets.use" = 1 for registered usergroup
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |

  Scenario: I open a guest's ticket as guest
    Given only the following Guest records exist:
      | #  | Name      | Email              |
      | u1 | User Name | user_1@deskpro.dev |
    And only the following Ticket records exist:
      | #        | Person | Department | Ref | Subject        | Date Last User Reply |
      | ticket_1 | {u1}   | {d1}       | ref | Ticket Subject | 2015-06-15 17:00:00  |
    And only the following TicketMessage records exist:
      | Ticket     | Person | Message |
      | {ticket_1} | {u1}   | text    |

    When I go to "/tickets/ref"
    Then I should be on "/login/set-password?email=my%40example.com"

  Scenario: I open a user's ticket as guest
    Given only the following User records exist:
      | #  | Name      | Email              |
      | u1 | User Name | user_1@deskpro.dev |
    And only the following Ticket records exist:
      | #        | Person | Department | Ref | Subject        | Date Last User Reply |
      | ticket_1 | {u1}   | {d1}       | ref | Ticket Subject | 2015-06-15 17:00:00  |
    And only the following TicketMessage records exist:
      | Ticket     | Person | Message |
      | {ticket_1} | {u1}   | text    |

    When I go to "/tickets/ref"
    Then I should be on "/login"

  Scenario: I open a guest's ticket as user
    Given only the following Guest records exist:
      | #  | Name      | Email              |
      | u1 | User Name | user_1@deskpro.dev |
    And I'm authenticated as user
    And only the following Ticket records exist:
      | #        | Person | Department | Ref | Subject        | Date Last User Reply |
      | ticket_1 | {u1}   | {d1}       | ref | Ticket Subject | 2015-06-15 17:00:00  |
    And only the following TicketMessage records exist:
      | Ticket     | Person | Message |
      | {ticket_1} | {u1}   | text    |

    When I go to "/tickets/ref"
    Then I should be on "/tickets/ref"
    And the response status code should be 403
