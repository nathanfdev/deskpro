@new
Feature: I want to check hidden department field if there is just one selectable department

  Background:
    Given I'm authenticated as user
    And I set permission "tickets.use" = 1 for "registered" usergroup
    And there are no Department records
    And the only default ticket layout exists with fields:
      | user_layout |
      | department  |

  Scenario: I have just one department
    Given only the following Department records exist:
      | # | Title      | Is Tickets Enabled |
      | d | Department | 1                  |
    And I grant the "{d}" department permission of tickets app for usergroup registered

    When I go to "/new-ticket"
    Then the "ticket[department]" hidden field should contain "{d}"

  Scenario: I have just one selectable department
    Given only the following Department records exist:
      | #  | Parent | Title          | Is Tickets Enabled |
      | d1 |        | Department 1   | 1                  |
      | d2 | {d1}   | Department 1a  | 1                  |
      | d3 | {d2}   | Department 1aa | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup registered
    And I grant the "{d2}" department permission of tickets app for usergroup registered
    And I grant the "{d3}" department permission of tickets app for usergroup registered

    When I go to "/new-ticket"
    Then the "ticket[department]" hidden field should contain "{d3}"

  Scenario: I have more than one selectable department
    Given only the following Department records exist:
      | #  | Parent | Title        | Is Tickets Enabled |
      | d1 |        | Department 1 | 1                  |
      | d2 |        | Department 2 | 1                  |
      | d3 |        | Department 3 | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup registered
    And I grant the "{d2}" department permission of tickets app for usergroup registered
    And I grant the "{d3}" department permission of tickets app for usergroup registered

    When I go to "/new-ticket"
    Then the "Department" field should contain ""
    And the response should contain "<select dpx-select id=\"ticket_department\" name=\"ticket[department]\""
    And the response should not contain "<input type=\"hidden\" id=\"ticket_department\" name=\"ticket[department]\""

  Scenario: I have more than one selectable department and pre-select department option
    Given only the following Department records exist:
      | #  | Parent | Title        | Is Tickets Enabled |
      | d1 |        | Department 1 | 1                  |
      | d2 |        | Department 2 | 1                  |
      | d3 |        | Department 3 | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup registered
    And I grant the "{d2}" department permission of tickets app for usergroup registered
    And I grant the "{d3}" department permission of tickets app for usergroup registered

    When I go to "/new-ticket?department_id={d3}"
    Then the "Department" field should contain "{d3}"
    And the response should contain "<select dpx-select id=\"ticket_department\" name=\"ticket[department]\""
    And the response should not contain "<input type=\"hidden\" id=\"ticket_department\" name=\"ticket[department]\""

  Scenario: I have more than one selectable department and pre-select department option with wrong department id
    Given only the following Department records exist:
      | #  | Parent | Title        | Is Tickets Enabled | Is Chat Enabled |
      | d1 |        | Department 1 | 1                  | 0               |
      | d2 |        | Department 2 | 1                  | 0               |
      | d3 |        | Department 3 | 0                  | 1               |
    And I grant the "{d1}" department permission of tickets app for usergroup registered
    And I grant the "{d2}" department permission of tickets app for usergroup registered
    And I grant the "{d3}" department permission of chat app for usergroup registered

    When I go to "/new-ticket?department_id={d3}"
    Then the "Department" field should contain ""
    And the response should contain "<select dpx-select id=\"ticket_department\" name=\"ticket[department]\""

  Scenario: I have more than one selectable department and open the form as "/new-ticket/{department_id}"
    Given only the following Department records exist:
      | #  | Parent | Title        | Is Tickets Enabled |
      | d1 |        | Department 1 | 1                  |
      | d2 |        | Department 2 | 1                  |
      | d3 |        | Department 3 | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup registered
    And I grant the "{d2}" department permission of tickets app for usergroup registered
    And I grant the "{d3}" department permission of tickets app for usergroup registered

    When I go to "/new-ticket/{d3}"
    Then the "ticket[department]" hidden field should contain "{d3}"

  Scenario: I have more than one selectable department and open the form as "/new-ticket/{department_id}" with wrong department id
    Given only the following Department records exist:
      | #  | Parent | Title        | Is Tickets Enabled | Is Chat Enabled |
      | d1 |        | Department 1 | 1                  | 0               |
      | d2 |        | Department 2 | 1                  | 0               |
      | d3 |        | Department 3 | 0                  | 1               |
    And I grant the "{d1}" department permission of tickets app for usergroup registered
    And I grant the "{d2}" department permission of tickets app for usergroup registered
    And I grant the "{d3}" department permission of chat app for usergroup registered

    When I go to "/new-ticket/{d3}"
    Then the "Department" field should contain ""
    And the response should contain "<select dpx-select id=\"ticket_department\" name=\"ticket[department]\""

  Scenario: I have department_id and hide_department in the request for the widget
    Given only the following Department records exist:
      | #  | Parent | Title        | Is Tickets Enabled |
      | d1 |        | Department 1 | 1                  |
      | d2 |        | Department 2 | 1                  |
      | d3 |        | Department 3 | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup registered
    And I grant the "{d2}" department permission of tickets app for usergroup registered
    And I grant the "{d3}" department permission of tickets app for usergroup registered

    When I go to "/portal/api/tickets/new?department_id={d2}&hide_department_field=1"
    Then the response should contain "<input type=\\"hidden\\" id=\\"ticket_department\\" name=\\"ticket[department]\\" value=\\"{d2}\\" \/>"

    When I go to "/portal/api/tickets/new?department_id={d2}"
    Then the response should not contain "<input type=\\"hidden\\" id=\\"ticket_department\\" name=\\"ticket[department]\\""
    And the response should contain "<select dpx-select id=\\"ticket_department\\" name=\\"ticket[department]\\""

    When I go to "/portal/api/tickets/new?hide_department_field=1"
    Then the response should not contain "<input type=\\"hidden\\" id=\\"ticket_department\\" name=\\"ticket[department]\\""
    And the response should contain "<select dpx-select id=\\"ticket_department\\" name=\\"ticket[department]\\""
