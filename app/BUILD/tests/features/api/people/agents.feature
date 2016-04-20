@chat-nav @people
Feature: /agents endpoint
  To retrieve DeskPRO agents
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @basic @reinstall
  Scenario: I get list of all agents
    Given I've just created a new agent with name "Alfred Zero"
    When I send a GET request to "/api/v2/agents"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data[0].name" should be equal to "Alfred Zero"

  Scenario: I soft-delete an agent and check agents list
    Given I've just created a new agent with name "Alfred One"
    And I send a DELETE request to the just created agent resource
    When I send a GET request to "/api/v2/agents"
    And the response should not contain "Alfred One"

  Scenario: I soft-delete an agent and check list of deleted agents
    Given I create a new agent with name "Alfred Two Correct"
    And I create a new agent with name "Alfred Two Mistake"
    And I send a DELETE request to the just created agent resource
    When I send a GET request to "/api/v2/agents?is_deleted=1"
    And the response should not contain "Alfred Two Correct"
    And the response should contain "Alfred Two Mistake"

  Scenario: I check combined list of soft-deleted and existing agents
    Given I create a new agent with name "Alfred Three"
    And I create a new agent with name "Alfred Four"
    And I send a DELETE request to the just created agent resource
    When I send a GET request to "/api/v2/agents?is_deleted=-1"
    And the response should contain "Alfred Three"
    And the response should contain "Alfred Four"

  Scenario: I try to remove an agent via people endpoint
    Given I've just created a new agent with name "Alfred Five"
    When I send a DELETE request to the last created agent resource via people endpoint
    Then the response status code should be 400

  Scenario: I convert an agent to person and check agents list
    Given I've just created a new agent with name "Alfred Six"
    And I send a DELETE request to the just created agent permissions resource
    And the response status code should be 204
    When I send a GET request to "/api/v2/agents?is_deleted=-1"
    Then the response should not contain "Alfred Six"

  Scenario: I convert an agent to person and check agent's tickets are unassigned
    Given I create an agent with 2 tickets
    When I send a DELETE request to the just created agent permissions resource
    And the response status code should be 204
    Then agent's ticket #1 should be unassigned
    And agent's ticket #2 should be unassigned

  Scenario: I convert an agent to person and check new person resource
    Given I've just created a new agent with name "Alfred Seven"
    And I send a DELETE request to the just created agent permissions resource
    When I send a GET request to the last created agent resource via people endpoint
    Then the response status code should be 200

  Scenario: I create a person and try to access the new resource via agents endpoint
    Given I've just created a new person with name "Alfred Eight"
    When I send a GET request to the last created person resource via agents endpoint
    Then the response status code should be 405

  Scenario: I give agent permissions to an existing user
    Given I've just created a new person with name "Alfred Nine"
    And I send a PUT request to the last created person permissions resource:
    """
{
  "agent": true
}
    """
    And the response status code should be 204
    When I send a GET request to "/api/v2/agents"
    Then the response status code should be 200
    And the response should contain "Alfred Nine"
