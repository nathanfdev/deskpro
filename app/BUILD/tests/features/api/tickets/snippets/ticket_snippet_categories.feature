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
    And the JSON node "data" should have 2 elements

    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].is_global" should be equal to 1
    And the JSON node "data[0].person" should be equal to 0
    And the JSON node "data[0].title" should be equal to "Ticket Category 1"

    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].is_global" should be equal to 0
    And the JSON node "data[1].person" should be equal to 1
    And the JSON node "data[1].title" should be equal to "Ticket Category 2"

  Scenario: I retrieve global categories
    When I send a GET request to "/api/v2/ticket_snippet_categories?global=1"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 1

  Scenario: I retrieve my categories
    When I send a GET request to "/api/v2/ticket_snippet_categories?my=1"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 2

  Scenario: I try to get category from another person
    When I send a GET request to "/api/v2/ticket_snippet_categories/3"
    Then the response status code should be 404

  Scenario: I try to get chat category
    When I send a GET request to "/api/v2/ticket_snippet_categories/4"
    Then the response status code should be 404

  Scenario: I get list of category snippets
    When I send a GET request to "/api/v2/ticket_snippet_categories/1/snippets"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements

    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].is_draft" should be equal to 1
    And the JSON node "data[0].person" should be equal to 0
    And the JSON node "data[0].category" should be equal to 1
    And the JSON node "data[0].shortcut_code" should be equal to "ticket_snippet1"

    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].is_draft" should be equal to 1
    And the JSON node "data[1].person" should be equal to 1
    And the JSON node "data[0].category" should be equal to 1
    And the JSON node "data[1].shortcut_code" should be equal to "ticket_snippet2"

  Scenario: I try to create a category with empty request
    When I send a POST request to "/api/v2/ticket_snippet_categories"
    Then the response status code should be 400
    And the JSON node "errors.fields.title.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.title.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I create a new ticket snippet category
    When I send a POST request to "/api/v2/ticket_snippet_categories" with body:
    """
{
  "title": "My Category"
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 7
    And the JSON node "data.person" should be equal to 1
    And the JSON node "data.title" should be equal to "My Category"
    And the JSON node "data.is_global" should be equal to 0

  Scenario: I modify category
    When I send a PUT request to "/api/v2/ticket_snippet_categories/7" with body:
    """
{
  "title": "My Edited Category",
  "is_global": 1
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/ticket_snippet_categories/7"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 7
    And the JSON node "data.person" should be equal to 1
    And the JSON node "data.title" should be equal to "My Edited Category"
    And the JSON node "data.is_global" should be equal to 1

  Scenario: I delete a category
    When I send a DELETE request to "/api/v2/ticket_snippet_categories/7"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/ticket_snippet_categories/7"
    Then the response status code should be 404
