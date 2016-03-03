@text-snippets
Feature: /ticket_snippets endpoint
  To CRUD DeskPRO ticket snippets
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve a list of text snippets
    When I send a GET request to "/api/v2/ticket_snippets"
    Then the response status code should be 200
    And the JSON node "data" should have 4 elements

    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].title" should be equal to "Ticket Snippet 1"
    And the JSON node "data[0].category" should be equal to 1
    And the JSON node "data[0].person" should be equal to 0
    And the JSON node "data[0].shortcut_code" should be equal to "ticket_snippet1"
    And the JSON node "data[0].is_draft" should be equal to 1

    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].title" should be equal to "Ticket Snippet 2"
    And the JSON node "data[1].category" should be equal to 1
    And the JSON node "data[1].person" should be equal to 1
    And the JSON node "data[1].shortcut_code" should be equal to "ticket_snippet2"
    And the JSON node "data[1].is_draft" should be equal to 1

    And the JSON node "data[2].id" should be equal to 3
    And the JSON node "data[2].title" should be equal to "Ticket Snippet 3"
    And the JSON node "data[2].category" should be equal to 2
    And the JSON node "data[2].person" should be equal to 1
    And the JSON node "data[2].shortcut_code" should be equal to "ticket_snippet3"
    And the JSON node "data[2].is_draft" should be equal to 0

    And the JSON node "data[3].id" should be equal to 5
    And the JSON node "data[3].title" should be equal to "Ticket Snippet 5"
    And the JSON node "data[3].category" should be equal to 3
    And the JSON node "data[3].person" should be equal to 1
    And the JSON node "data[3].shortcut_code" should be equal to "ticket_snippet5"
    And the JSON node "data[3].is_draft" should be equal to 1

  Scenario: I retrieve a list of text snippets filtered by category
    When I send a GET request to "/api/v2/ticket_snippets?category=1"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[1].id" should be equal to 2

    When I send a GET request to "/api/v2/ticket_snippets?category=3"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 5

  Scenario: I filter by person
    When I send a GET request to "/api/v2/ticket_snippets?global=1"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 1

    When I send a GET request to "/api/v2/ticket_snippets?my=1"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to 2
    And the JSON node "data[1].id" should be equal to 3
    And the JSON node "data[2].id" should be equal to 5

  Scenario: I filter by draft
    When I send a GET request to "/api/v2/ticket_snippets?draft=1"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[2].id" should be equal to 5

    When I send a GET request to "/api/v2/ticket_snippets?draft=0"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 3

  Scenario: I try to create a ticket snippet with empty request
    When I send a POST request to "/api/v2/ticket_snippets"
    Then the response status code should be 400

    And the JSON node "errors.fields.title.errors[0].code" should be equal to "too_few_elements"
    And the JSON node "errors.fields.title.errors[0].message" should be equal to "This collection should contain 1 elements or more."

    And the JSON node "errors.fields.snippet.errors[0].code" should be equal to "too_few_elements"
    And the JSON node "errors.fields.snippet.errors[0].message" should be equal to "This collection should contain 1 elements or more."

    And the JSON node "errors.fields.category.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.category.errors[0].message" should be equal to "This value should not be blank."

    And the JSON node "errors.fields.shortcut_code.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.shortcut_code.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I try to create a ticket snippet with wrong object lang data
    When I send a POST request to "/api/v2/ticket_snippets" with body:
    """
{
  "title": [
    {
      "language": 0,
      "value": ""
    }
  ],
  "snippet": [
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

    And the JSON node "errors.fields.snippet.fields.snippet_0.fields.language.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.snippet.fields.snippet_0.fields.language.errors[0].message" should be equal to "One or more of the given values is invalid."
    And the JSON node "errors.fields.snippet.fields.snippet_0.fields.value.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.snippet.fields.snippet_0.fields.value.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I add a new text snippet
    When I send a POST request to "/api/v2/ticket_snippets" with body:
    """
{
  "category": 1,
  "shortcut_code": "my_snippet",
  "title": [
    {
      "language": 1,
      "value": "My Snippet"
    }
  ],
  "snippet": [
    {
      "language": 1,
      "value": "My Snippet Content"
    }
  ]
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 11
    And the JSON node "data.title" should be equal to "My Snippet"
    And the JSON node "data.person" should be equal to 1
    And the JSON node "data.shortcut_code" should be equal to "my_snippet"
    And the JSON node "data.is_draft" should be equal to 0

  Scenario: I modify text snippet
    When I send a PUT request to "/api/v2/ticket_snippets/11" with body:
    """
{
  "category": 2,
  "is_draft": 1,
  "is_global": 1
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/ticket_snippets/11"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 11
    And the JSON node "data.title" should be equal to "My Snippet"
    And the JSON node "data.person" should be equal to 0
    And the JSON node "data.shortcut_code" should be equal to "my_snippet"
    And the JSON node "data.is_draft" should be equal to 1
