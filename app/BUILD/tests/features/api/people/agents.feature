@chat-nav
Feature: /agents endpoint
  To retrieve DeskPRO agents
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @basic @reinstall
  Scenario: I get list of all agents
    When I send a GET request to "/api/v2/agents"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[2].name" should be equal to "Link Admin"

