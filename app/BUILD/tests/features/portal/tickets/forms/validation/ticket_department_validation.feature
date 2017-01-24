@new
Feature: New ticket form validation
  I want to check department field validation

  Background:
    Given I'm authenticated as user
    And I have only default brand
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

  Scenario: I check empty department
    When I go to "/new-ticket"
    And I press "Submit"
    Then "ticket[department]" form field should have error with the phrase "portal.forms.error_required"
    And "ticket[department]" form field should have 1 error

  Scenario Outline: I check leaf department
    When I go to "/new-ticket"
    And I select "<department>" from "Department"
    And I press "Submit"
    Then "ticket[department]" form field should have error with the phrase "portal.forms.error_not_assignable_department"
    And "ticket[department]" form field should have 1 error

    Examples:
      | department |
      | {d1}       |
      | {d2}       |
