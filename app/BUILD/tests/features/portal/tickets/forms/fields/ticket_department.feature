@new
Feature: New ticket form
  I want to check department submission

  Background:
    Given I'm authenticated as user
    And I have only default brand
    And user has defaultBrand brand
    And the "{defaultBrand}" setting "core_tickets.use_ref" is set to "1"
    And the following languages are enabled:
      | default |
    And the only default ticket layout exists with fields:
      | user_layout |
    And I disable anti-abuse rate limiting
    And I set permission "tickets.use" = 1 for "registered" usergroup
    And default everyone user group exists
    And only the following Department records exist:
      | #  | Parent | Title          | Brands           | Is Tickets Enabled |
      | d1 | NULL   | Department 1   | [{defaultBrand}] | 1                  |
      | d2 | {d1}   | Department 1a  | [{defaultBrand}] | 1                  |
      | d3 | {d1}   | Department 1b  | [{defaultBrand}] | 1                  |
      | d4 | {d2}   | Department 1aa | [{defaultBrand}] | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup everyone
    And I grant the "{d2}" department permission of tickets app for usergroup everyone
    And I grant the "{d3}" department permission of tickets app for usergroup everyone
    And I grant the "{d4}" department permission of tickets app for usergroup everyone

  Scenario Outline: I check department submission
    Given only the following Ticket records exist:
      | #        | Person | Department | Ref | Subject        | Date Last User Reply |
      | ticket_1 | {user} | {d2}       | ref | Ticket Subject | 2015-06-15 17:00:00  |
    And only the following TicketMessage records exist:
      | Ticket     | Person | Message |
      | {ticket_1} | {user} | text    |

    When I go to "/tickets/ref/edit"
    And I select "<department>" from "Department"
    And I press "Save"
    Then I should be on "/tickets/ref"

    When I go to "/tickets/ref/edit"
    Then the "ticket[department]" field should contain "<department>"

    Examples:
      | department |
      | {d3}       |
      | {d4}       |
