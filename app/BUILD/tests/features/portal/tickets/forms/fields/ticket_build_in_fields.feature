@new
Feature: New ticket form
  I want to check built in fields

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
    And I grant the "{d1}" department permission of tickets app for usergroup everyone

  Scenario Outline: I check leaf node validation
    Given  the setting "core.<use_setting_name>" is set to 1
    And only the following <entity_type> records exist:
      | #  | Title   |
      | p1 | Title 1 |
      | p2 | Title 2 |
    And the only default ticket layout exists with fields:
      | user_layout  |
      | <field_name> |
    And only the following Ticket records exist:
      | #        | Person | Department | Ref | Subject        | Date Last User Reply |
      | ticket_1 | {user} | {d1}       | ref | Ticket Subject | 2015-06-15 17:00:00  |
    And only the following TicketMessage records exist:
      | Ticket     | Person | Message |
      | {ticket_1} | {user} | text    |

    When I go to "/tickets/ref/edit"
    And I select "{p1}" from "ticket[<field_name>]"
    And I press "Save"
    Then I should be on "/tickets/ref"

    When I go to "/tickets/ref/edit"
    Then the "ticket[<field_name>]" field should contain "{p1}"

    Examples:
      | entity_type    | field_name | use_setting_name    |
      | Product        | product    | use_product         |
      | TicketCategory | category   | use_ticket_category |
