@new
Feature: New ticket form validation
  I want to check ticket participants

  Background:
    Given no Person records exist
    And I'm authenticated as user
    And a user with "user_1@deskpro.dev" email exists
    And an agent with "agent_1@deskpro.dev" email exists
    And an agent with "agent_2@deskpro.dev" email exists
    And the only default ticket layout exists with fields:
      | user_layout |
      | cc          |
    And the setting "core_tickets.add_agent_ccs" is set to 0
    And I go to "/new-ticket"

  Scenario: I try to add person with wrong role
    When I fill in "ticket[cc]" with "agent_1@deskpro.dev"
    And I press "Submit"
    Then the "ticket[cc]" field should contain "agent_1@deskpro.dev"
    And "ticket[cc]" form field should have 1 error
    And "ticket[cc]" form field should have error with the phrase "agent_1@deskpro.dev"
    And "ticket[cc]" form field should have error with the phrase "user"

  Scenario: I try to add unknown user as cc
    When I fill in "ticket[cc]" with "unkkwon_person@deskpro.dev"
    And I press "Submit"
    Then the "ticket[cc]" field should contain "unkkwon_person@deskpro.dev"
    And "ticket[cc]" form field should have 0 errors

  Scenario: One of the emails failed validation
    When I fill in "ticket[cc]" with "user_1@deskpro.dev,agent_1@deskpro.dev"
    And I press "Submit"
    Then the "ticket[cc]" field should contain "user_1@deskpro.dev,agent_1@deskpro.dev"
    And "ticket[cc]" form field should have 1 error
    And "ticket[cc]" form field should have error with the phrase "agent_1@deskpro.dev"
    And "ticket[cc]" form field should have error with the phrase "user."

  Scenario: I check more than one email were failed
    When I fill in "ticket[cc]" with "agent_1@deskpro.dev,agent_2@deskpro.dev"
    And I press "Submit"
    Then the "ticket[cc]" field should contain "agent_1@deskpro.dev,agent_2@deskpro.dev"
    And "ticket[cc]" form field should have 2 errors
    And "ticket[cc]" form field should have error with the phrase "agent_1@deskpro.dev"
    And "ticket[cc]" form field should have error with the phrase "agent_2@deskpro.dev"

  Scenario: I check participant with bad email
    When I fill in "ticket[cc]" with "bad_email"
    And I press "Submit"
    Then the "ticket[cc]" field should contain "bad_email"
    And "ticket[cc]" form field should have 1 error
    And "ticket[cc]" form field should have error with the phrase "bad_email"

  Scenario: I check that I can add agents as CCs
    Given the setting "core_tickets.add_agent_ccs" is set to 1
    When I fill in "ticket[cc]" with "user_1@deskpro.dev,agent_1@deskpro.dev"
    And I press "Submit"
    Then the "ticket[cc]" field should contain "user_1@deskpro.dev,agent_1@deskpro.dev"
    And "ticket[cc]" form field should have 0 errors
