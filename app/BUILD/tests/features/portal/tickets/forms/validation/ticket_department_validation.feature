@new
Feature: New ticket form validation
  I want to check department field validation

  Background:
    Given I'm authenticated as user

  Scenario: I check empty department
    Given default everyone user group exits
    And only the following Department records exist:
      | #  | Title        | Is Tickets Enabled |
      | d1 | Department 1 | 1                  |
      | d2 | Department 2 | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup everyone
    And I grant the "{d2}" department permission of tickets app for usergroup everyone

    When I go to "/new-ticket"
    And I press "Submit"
    Then "ticket[department]" form field should have error with the phrase "This value is required"
    And "ticket[department]" form field should have 1 error

  Scenario: I check leaf department
    Given default everyone user group exits
    And only the following Department records exist:
      | #  | Parent | Title        | Is Tickets Enabled |
      | d1 | NULL   | Department 1 | 1                  |
      | d2 | {d1}   | Department 2 | 1                  |
      | d3 | {d1}   | Department 3 | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup everyone
    And I grant the "{d2}" department permission of tickets app for usergroup everyone
    And I grant the "{d3}" department permission of tickets app for usergroup everyone

    When I go to "/new-ticket"
    And I select "Department 1" from "Department"
    And I press "Submit"
    Then "ticket[department]" form field should have error with the phrase "This value is required"
    And "ticket[department]" form field should have 1 error
