@text-snippets
Feature: /ticket_snippet_categories endpoint
  To CRUD DeskPRO ticket snippet categories
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

  Scenario: I filter by language
    When I send a GET request to "/api/v2/ticket_snippet_categories?language=en_US"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 1

    When I send a GET request to "/api/v2/ticket_snippet_categories?language=fre"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 2

    When I send a GET request to "/api/v2/ticket_snippet_categories?language=2"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 2

    When I send a GET request to "/api/v2/ticket_snippet_categories?language=Unknown_Lang"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements

    When I send a GET request to "/api/v2/ticket_snippet_categories?language=404"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements

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

  Scenario: I get list of category snippets from another person
    When I send a GET request to "/api/v2/ticket_snippet_categories/3/snippets"
    Then the response status code should be 404

  Scenario: I try to create a category with empty request
    When I send a POST request to "/api/v2/ticket_snippet_categories"
    Then the response status code should be 400
    And the JSON node "errors.fields.title.errors[0].code" should be equal to "too_few_elements"
    And the JSON node "errors.fields.title.errors[0].message" should be equal to "This collection should contain 1 elements or more."

  Scenario: I try to create a category with wrong object lang format
    When I send a POST request to "/api/v2/ticket_snippet_categories" with body:
    """
{
  "title": [
    {
      "language": 1,
      "value": "My Category"
    },
    "My Category (french)"
  ]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.title.fields.title_1.errors[0].code" should be equal to "invalid_data_type"
    And the JSON node "errors.fields.title.fields.title_1.errors[0].message" should be equal to "This data type is not is data type that was expected."

  Scenario: I try to create a category with empty object lang data
    When I send a POST request to "/api/v2/ticket_snippet_categories" with body:
    """
{
  "title": [
    {
      "language": 0,
      "value": ""
    }
  ]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.title.fields.title_0.fields.language.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.title.fields.title_0.fields.language.errors[0].message" should be equal to "One or more of the given values is invalid."
    And the JSON node "errors.fields.title.fields.title_0.fields.value.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.title.fields.title_0.fields.value.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I create a new ticket snippet category
    When I send a POST request to "/api/v2/ticket_snippet_categories" with body:
    """
{
  "title": [
    {
      "language": 1,
      "value": "My Category"
    },
    {
      "language": 2,
      "value": "My Category (french)"
    },
    {
      "language": 3,
      "value": "My Category (russian)"
    }
  ]
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
  "title": [
    {
      "language": 1,
      "value": "My Edited Category"
    }
  ],
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
