@new
Feature: New ticket dupe check

  Background:
    Given no Person records exist
    And "user@deskpro.dev" user exists
    And I disable anti-abuse rate limiting
    And I set permission "tickets.use" = 1 for registered usergroup
    And I have only default brand
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
      | d2 | Department 2 | [{defaultBrand}] | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup everyone
    And I grant the "{d2}" department permission of tickets app for usergroup everyone

  Scenario: I try to create dupe ticket and get error message
    Given only the following Ticket records exist:
      | #        | Person             | Department | Brand          | Ref | Subject        | Date Last User Reply | Message              | Date Created   |
      | ticket_1 | {user@deskpro.dev} | {d1}       | {defaultBrand} | ref | Ticket Subject | 2015-06-15 17:00:00  | Ticket message text. | NOW() - 1 hour |

    When I go to "/new-ticket"
    And I fill in "ticket_person_user_name" with "Name"
    And I fill in "ticket_person_user_email_email" with "user@deskpro.dev"
    And I select "Department 1" from "Department"
    And I fill in "Subject" with "Ticket Subject"
    And I fill in "Message" with "Ticket message text."
    And I press "Submit"
    Then the response should contain "Duplicate ticket."

  Scenario: I try to create dupe ticket and redirect to success page with old ref
    Given only the following Ticket records exist:
      | #        | Person             | Department | Brand          | Ref | Subject        | Date Last User Reply | Message              | Date Created  |
      | ticket_1 | {user@deskpro.dev} | {d1}       | {defaultBrand} | ref | Ticket Subject | 2015-06-15 17:00:00  | Ticket message text. | NOW() - 1 min |

    When I go to "/new-ticket"
    And I fill in "ticket_person_user_name" with "Name"
    And I fill in "ticket_person_user_email_email" with "user@deskpro.dev"
    And I select "Department 1" from "Department"
    And I fill in "Subject" with "Ticket Subject"
    And I fill in "Message" with "Ticket message text."
    And I press "Submit"

    Then the url should match "/thank-you/ref"
