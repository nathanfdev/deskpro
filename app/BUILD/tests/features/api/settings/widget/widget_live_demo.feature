@new
Feature: Widget setup
  Sample online agents for demo preview

  Background:
    Given I remove all Person records
    And I'm authenticated as admin

  Scenario: I retrieve a list of real people
    Given "person_1@deskpro.dev" user exists
    And "person_2@deskpro.dev" user exists
    And "person_3@deskpro.dev" user exists
    And "agent_1@deskpro.dev" agent exists
    And "agent_2@deskpro.dev" agent exists

    When I send a GET request to "/api/v2/widget/live_demo/sample_state"
    Then the response status code should be 200
    And the JSON node "data.people.agents" should have 3 elements
    And the JSON node "data.people.users" should have 3 elements

  Scenario: I have less than required real people
    When I send a GET request to "/api/v2/widget/live_demo/sample_state"
    Then the response status code should be 200
    And the JSON node "data.people.agents" should have 3 elements
    And the JSON node "data.people.users" should have 3 elements

  Scenario: I have chat sample state
    When I send a GET request to "/api/v2/widget/live_demo/sample_state"
    Then the response status code should be 200
    And the JSON node "data.chat" should exist
    And the JSON node "data.chat.info" should exist
    And the JSON node "data.chat.messages" should exist
    And the JSON node "data.chat.info.id" should be equal to 1
    And the JSON node "data.chat.messages" should have 4 elements
