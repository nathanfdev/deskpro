@tickets
Feature: /ticket_macros endpoint
  To CRUD DeskPRO tickets
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve a list of macros
    When I send a GET request to "/api/v2/ticket_macros"
    And the response status code should be 200
    And the JSON node "data" should have 4 elements

    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].person" should be equal to 1
    And the JSON node "data[0].title" should be equal to "Ticket macro 1"
    And the JSON node "data[0].is_enabled" should be equal to 1
    And the JSON node "data[0].is_global" should be equal to 1
    And the JSON node "data[0].actions" should have 2 elements
    And the JSON node "data[0].actions[0].type" should be equal to "agent"
    And the JSON node "data[0].actions[0].options.agent" should be equal to "-1"
    And the JSON node "data[0].actions[1].type" should be equal to "department"
    And the JSON node "data[0].actions[1].options.department" should be equal to 1

    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].person" should be equal to 1
    And the JSON node "data[1].title" should be equal to "Ticket macro 2"
    And the JSON node "data[1].is_enabled" should be equal to 1
    And the JSON node "data[1].is_global" should be equal to 0
    And the JSON node "data[1].actions" should have 2 elements
    And the JSON node "data[1].actions[0].type" should be equal to "add_labels"
    And the JSON node "data[1].actions[0].options.labels" should have 3 elements
    And the JSON node "data[1].actions[0].options.labels[0]" should be equal to "label1"
    And the JSON node "data[1].actions[0].options.labels[1]" should be equal to "label2"
    And the JSON node "data[1].actions[0].options.labels[2]" should be equal to "label3"
    And the JSON node "data[1].actions[1].type" should be equal to "language"
    And the JSON node "data[1].actions[1].options.language" should be equal to 2

    And the JSON node "data[2].id" should be equal to 3
    And the JSON node "data[2].person" should be equal to 1
    And the JSON node "data[2].title" should be equal to "Ticket macro 3"
    And the JSON node "data[2].is_enabled" should be equal to 1
    And the JSON node "data[2].is_global" should be equal to 0
    And the JSON node "data[2].actions" should have 3 elements
    And the JSON node "data[2].actions[0].type" should be equal to "add_labels"
    And the JSON node "data[2].actions[0].options.labels" should have 3 elements
    And the JSON node "data[2].actions[0].options.labels[0]" should be equal to "label4"
    And the JSON node "data[2].actions[0].options.labels[1]" should be equal to "label5"
    And the JSON node "data[2].actions[0].options.labels[2]" should be equal to "label6"
    And the JSON node "data[2].actions[1].type" should be equal to "language"
    And the JSON node "data[2].actions[1].options.language" should be equal to 2
    And the JSON node "data[2].actions[2].type" should be equal to "department"
    And the JSON node "data[2].actions[2].options.department" should be equal to 1

    And the JSON node "data[3].id" should be equal to 5
    And the JSON node "data[3].person" should be equal to 2
    And the JSON node "data[3].title" should be equal to "Ticket macro 5"
    And the JSON node "data[3].is_enabled" should be equal to 1
    And the JSON node "data[3].is_global" should be equal to 1
    And the JSON node "data[3].actions[0].type" should be equal to "status"
    And the JSON node "data[3].actions[0].options.status" should be equal to "awaiting_agent"

  Scenario: I get a macro
    When I send a GET request to "/api/v2/ticket_macros/1"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.title" should be equal to "Ticket macro 1"

  Scenario: I try to get not existing macro
    When I send a GET request to "/api/v2/ticket_macros/404"
    Then the response status code should be 404

  Scenario: I try to get a macro from another user
    When I send a GET request to "/api/v2/ticket_macros/4"
    Then the response status code should be 404

  Scenario: I apply a macro
    When I send a GET request to "/api/v2/tickets/1"
    Then the response status code should be 200
    And the JSON node "data.language" should be equal to 0
    And the JSON node "data.labels" should have 0 elements

    When I send a POST request to "/api/v2/ticket_macros/2/apply/1"
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/1"
    Then the response status code should be 200
    And the JSON node "data.language" should be equal to 2
    And the JSON node "data.labels" should have 3 elements
    And the JSON node "data.labels[0]" should be equal to "label1"
    And the JSON node "data.labels[1]" should be equal to "label2"
    And the JSON node "data.labels[2]" should be equal to "label3"

  Scenario: I check macro validation
    When I send a POST request to "/api/v2/ticket_macros/1/apply/1"
    Then the response status code should be 400