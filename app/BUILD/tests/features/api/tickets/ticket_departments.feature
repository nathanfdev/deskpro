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
    Then the response status code should be 200

    And the JSON node "data" should have 1 elements
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].title" should be equal to "sales"

  Scenario: I get ticket department
    When I send a GET request to "/api/v2/ticket_departments/1"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.title" should be equal to "sales"

  Scenario: I try to get chat department
    When I send a GET request to "/api/v2/ticket_departments/2"
    Then the response status code should be 404

  Scenario: I get ticket department agents
    When I send a GET request to "/api/v2/ticket_departments/1/agents"
    Then the response status code should be 200

  Scenario: I try to create a new ticket department with empty request
    When I send a POST request to "/api/v2/ticket_departments"
    Then the response status code should be 400
    And the JSON node "errors.fields.title.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.title.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I create a new ticket department
    When I send a POST request to "/api/v2/ticket_departments" with body:
    """
{
  "title": "new department"
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 3
    And the JSON node "data.title" should be equal to "new department"
    And the JSON node "data.user_title" should be equal to "new department"
    And the JSON node "data.display_order" should be equal to 0
    And the JSON node "data.is_tickets_enabled" should be equal to 1
    And the JSON node "data.is_chat_enabled" should be equal to 0

  Scenario: I edit ticket department
    When I send a PUT request to "/api/v2/ticket_departments/3" with body:
    """
{
  "user_title": "user title for new department"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/ticket_departments/3"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 3
    And the JSON node "data.title" should be equal to "new department"
    And the JSON node "data.user_title" should be equal to "user title for new department"

  Scenario: I try to edit chat department
    When I send a PUT request to "/api/v2/ticket_departments/2"
    Then the response status code should be 404
