@new
Feature: New ticket form validation
  I want to check extra fields validation on change layout

  Background:
    Given I'm authenticated as user
    And only the following Department records exist:
      | #  | Title        | Is Tickets Enabled |
      | d1 | Department 1 | 1                  |
      | d2 | Department 2 | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup everyone
    And I grant the "{d2}" department permission of tickets app for usergroup everyone
    And there are no TicketLayout records

  Scenario: I check unexpected field on layout
    Given the ticket layout exists for "d1" department with fields:
      | user_layout |
      | cc          |
    And I go to "/new-ticket"
    And I select "Department 1" from "Department"
    And I press "Submit"

    When I select "Department 2" from "Department"
    And I fill in "ticket[cc]" with "person@deskpro.dev"
    And I press "Submit"
    Then the response should contain "There are some errors with your submission."
