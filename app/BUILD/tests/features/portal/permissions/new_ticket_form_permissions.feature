@new
Feature: New ticket form permissions

  Background:
    Given I'm authenticated as user
    And I clear department permissions for user
    And I clear permissions for user

  Scenario: I open new ticket form using usergroup permissions
    Given I set permission "tickets.use" = 1 for registered usergroup
    When I go to "/new-ticket"
    And I should see the "ticket[subject]" field

  Scenario: I try to open new ticket form w/o permission
    Given I set permission "tickets.use" = 0 for registered usergroup
    And I set permission "tickets.use" = 0 for everyone usergroup
    When I go to "/new-ticket"
    Then the response should contain "Sorry, but you do not have access to view this page."
