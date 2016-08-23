@new
Feature: New ticket form validation
  I want to check ticket participants

  Background:
    Given I'm authenticated as user
    And a user with "user_1@deskpro.dev" email exists
    And an agent with "agent_1@deskpro.dev" email exists
    And the only default ticket layout exists with fields:
      | user_layout |
      | followers   |
      | cc          |
    And the setting "core_tickets.add_agent_ccs" is set to 0
    And I go to "/new-ticket"

  Scenario Outline: I try to add person with wrong role
    When I fill in "ticket[<type>]" with "<email>"
    And I press "Submit"
    Then the "ticket[<type>]" field should contain "<email>"
    And "ticket[<type>]" form field should have 1 error
    And "ticket[<type>]" form field should have error with the phrase "<email>"
    And "ticket[<type>]" form field should have error with the phrase "<role>"

    Examples:
      | type      | email               | role  |
      | cc        | agent_1@deskpro.dev | user  |
      | followers | user_1@deskpro.dev  | agent |

  Scenario Outline: I try to add unknown user as cc
    When I fill in "ticket[<type>]" with "unkkwon_person@deskpro.dev"
    And I press "Submit"
    Then the "ticket[<type>]" field should contain "unkkwon_person@deskpro.dev"
    And "ticket[<type>]" form field should have 1 error
    And "ticket[<type>]" form field should have error with the phrase "unkkwon_person@deskpro.dev"
    And "ticket[<type>]" form field should have error with the phrase "not found."

    Examples:
      | type      |
      | cc        |
      | followers |

  Scenario Outline: One of the emails failed validation
    When I fill in "ticket[<type>]" with "user_1@deskpro.dev,agent_1@deskpro.dev"
    And I press "Submit"
    Then the "ticket[<type>]" field should contain "user_1@deskpro.dev,agent_1@deskpro.dev"
    And "ticket[<type>]" form field should have 1 error
    And "ticket[<type>]" form field should have error with the phrase "<email>"
    And "ticket[<type>]" form field should have error with the phrase "<role>."

    Examples:
      | type      | email               | role  |
      | cc        | agent_1@deskpro.dev | user  |
      | followers | user_1@deskpro.dev  | agent |

  Scenario Outline: I check more than one email were failed
    When I fill in "ticket[<type>]" with "<given_role>_1@deskpro.dev,<given_role>_2@deskpro.dev"
    And I press "Submit"
    Then the "ticket[<type>]" field should contain "<given_role>_1@deskpro.dev,<given_role>_2@deskpro.dev"
    And "ticket[<type>]" form field should have 2 errors
    And "ticket[<type>]" form field should have error with the phrase "<given_role>_1@deskpro.dev"
    And "ticket[<type>]" form field should have error with the phrase "<given_role>_2@deskpro.dev"

    Examples:
      | type      | given_role |
      | cc        | agent      |
      | followers | user       |

  Scenario Outline: I check participant with bad email
    When I fill in "ticket[<type>]" with "bad_email"
    And I press "Submit"
    Then the "ticket[<type>]" field should contain "bad_email"
    And "ticket[<type>]" form field should have 1 error
    And "ticket[<type>]" form field should have error with the phrase "bad_email"
    And "ticket[<type>]" form field should have error with the phrase "not found."

    Examples:
      | type      |
      | cc        |
      | followers |

  Scenario: I check that I can add agents as CCs
    Given the setting "core_tickets.add_agent_ccs" is set to 1
    When I fill in "ticket[cc]" with "user_1@deskpro.dev,agent_1@deskpro.dev"
    And I press "Submit"
    Then the "ticket[cc]" field should contain "user_1@deskpro.dev,agent_1@deskpro.dev"
    And "ticket[cc]" form field should have 0 errors

  Scenario: I check that I can't add users as followers
    Given the setting "core_tickets.add_agent_ccs" is set to 1
    When I fill in "ticket[followers]" with "user_1@deskpro.dev,agent_1@deskpro.dev"
    And I press "Submit"
    Then the "ticket[followers]" field should contain "user_1@deskpro.dev,agent_1@deskpro.dev"
    And "ticket[followers]" form field should have 1 error
    And "ticket[followers]" form field should have error with the phrase "user_1@deskpro.dev"
    And "ticket[followers]" form field should have error with the phrase "agent."
