Feature: /ticket_departments endpoint
  To CRUD DeskPRO ticket departments
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve a list of ticket departments
    When I send a GET request to "/api/v2/ticket_departments"
    And the response status code should be 200

    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].title" should be equal to "sales"
    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].title" should be equal to "support"

  Scenario: I get ticket department
    When I send a GET request to "/api/v2/ticket_departments/1"
    And the response status code should be 200

    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.title" should be equal to "sales"

  Scenario: I get ticket department agents
    When I send a GET request to "/api/v2/ticket_departments/1/agents"
    And the response status code should be 200