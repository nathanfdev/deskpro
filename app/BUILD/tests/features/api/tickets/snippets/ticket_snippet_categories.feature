@text-snippets
Feature: /text_snippet_categories endpoint
  To CRUD DeskPRO text snippet categories
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve a list of ticket snippet categories
    When I send a GET request to "/api/v2/ticket_snippet_categories"
    Then the response status code should be 200

  Scenario: I try to create a category with empty request
    When I send a POST request to "/api/v2/ticket_snippet_categories"
    Then the response status code should be 400
    And the JSON node "errors.fields.title.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.title.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I create a new ticket snippet category
    When I send a POST request to "/api/v2/ticket_snippet_categories" with body:
    """
{
  "title": "My Category",
  "person": 1
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.person" should be equal to 1
    And the JSON node "data.title" should be equal to "My Category"
    And the JSON node "data.is_global" should be equal to 0

  Scenario: I modify category
    When I send a PUT request to "/api/v2/ticket_snippet_categories/1" with body:
    """
{
  "title": "My Edited Category",
  "person": 2,
  "is_global": 1
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/ticket_snippet_categories/1"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.person" should be equal to 2
    And the JSON node "data.title" should be equal to "My Edited Category"
    And the JSON node "data.is_global" should be equal to 1
