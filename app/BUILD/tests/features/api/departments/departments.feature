@chat-nav @tasks-nav
Feature: /ticket_departments endpoint
  To retrieve DeskPRO departments
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I get paginated list of departments
    When I send a GET request to "/api/v2/ticket_departments"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].title" should be equal to "sales"
    And the JSON node "meta.pagination.count" should be equal to 2

  Scenario: I get a single department
    When I send a GET request to "/api/v2/ticket_departments/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.id" should be equal to "1"
    And the JSON node "data.title" should be equal to "sales"

  Scenario: I get a single department agents list
    When I send a GET request to "/api/v2/ticket_departments/1/agents"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist