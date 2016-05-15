Feature: /ticket_snippets endpoint
  To CRUD DeskPRO ticket snippets
  As an API user
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I retrieve a list of text snippets
    When I send a GET request to "/api/v2/ticket_snippets"
    Then the response status code should be 200
    And the JSON node "data" should have 4 elements

    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].title" should be equal to "Ticket Snippet {{ ticket.subject }} (en) 1"
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

    And the JSON node "data[3].id" should be equal to 11
    And the JSON node "data[3].title" should be equal to 0
    And the JSON node "data[3].category" should be equal to 2
    And the JSON node "data[3].person" should be equal to 1
    And the JSON node "data[3].shortcut_code" should be equal to "ticket_snippet_wo_content"
    And the JSON node "data[3].is_draft" should be equal to 1

    When I send a GET request to "/api/v2/ticket_snippets/1"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/ticket_snippets/2"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/ticket_snippets/5"
    Then the response status code should be 404

  Scenario: I retrieve a list of text snippets filtered by category
    When I send a GET request to "/api/v2/ticket_snippets?category=1"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[1].id" should be equal to 2

    When I send a GET request to "/api/v2/ticket_snippets?category=2"
    Then the response status code should be 200
    And the JSON node "data" should have 2 element
    And the JSON node "data[0].id" should be equal to 3
    And the JSON node "data[1].id" should be equal to 11

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
    And the JSON node "data[2].id" should be equal to 11

  Scenario: I filter by draft
    When I send a GET request to "/api/v2/ticket_snippets?draft=1"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[2].id" should be equal to 11

    When I send a GET request to "/api/v2/ticket_snippets?draft=0"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 3

  Scenario: I filter by language
    When I send a GET request to "/api/v2/ticket_snippets?language=en_US"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 1

    When I send a GET request to "/api/v2/ticket_snippets?language=fre"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[1].id" should be equal to 2

    When I send a GET request to "/api/v2/ticket_snippets?language=3"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 3

    When I send a GET request to "/api/v2/ticket_snippets?language=Unknown_Lang"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements

    When I send a GET request to "/api/v2/ticket_snippets?language=404"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements

  Scenario: I retrieve ticket snippet content
    When I send a GET request to "/api/v2/ticket_snippets/1/content"
    Then the response status code should be 200
    And the JSON node "data.en_US.title" should be equal to the string "Ticket Snippet {{ ticket.subject }} (en) 1"
    And the JSON node "data.en_US.content" should be equal to the string "Ticket Snippet Content {{ ticket.person.name }} (en) 1"
    And the JSON node "data.fr.title" should be equal to the string "Ticket Snippet (fr) 1"
    And the JSON node "data.fr.content" should be equal to the string "Ticket Snippet Content (fr) 1"

  Scenario: I retrieve ticket snippet content with replacements
    When I send a GET request to "/api/v2/ticket_snippets/1/content?ticket=2"
    Then the response status code should be 200
    And the JSON node "data.en_US.title" should be equal to "Ticket Snippet Ticket #1 (en) 1"
    And the JSON node "data.en_US.content" should be equal to "Ticket Snippet Content Ganon User (en) 1"

  Scenario: I retrieve ticket snippets with sideloading
    When I send a GET request to "/api/v2/ticket_snippets?include=text_snippet_content"
    Then the response status code should be 200
    And the JSON node "linked.text_snippet_content.1.en_US.title" should be equal to the string "Ticket Snippet {{ ticket.subject }} (en) 1"
    And the JSON node "linked.text_snippet_content.1.en_US.content" should be equal to the string "Ticket Snippet Content {{ ticket.person.name }} (en) 1"
    And the JSON node "linked.text_snippet_content.1.fr.title" should be equal to the string "Ticket Snippet (fr) 1"
    And the JSON node "linked.text_snippet_content.1.fr.content" should be equal to the string "Ticket Snippet Content (fr) 1"
    And the JSON node "linked.text_snippet_content.2.fr.title" should be equal to the string "Ticket Snippet 2"
    And the JSON node "linked.text_snippet_content.2.fr.content" should be equal to the string "Ticket Snippet Content 2"

    When I send a GET request to "/api/v2/ticket_snippets?include=text_snippet_content&ticket=2"
    Then the response status code should be 200
    And the JSON node "linked.text_snippet_content.1.en_US.title" should be equal to the string "Ticket Snippet Ticket #1 (en) 1"
    And the JSON node "linked.text_snippet_content.1.en_US.content" should be equal to the string "Ticket Snippet Content Ganon User (en) 1"

  Scenario: I sideload snippet w/o content
    When I send a GET request to "/api/v2/ticket_snippets/11/content"
    Then the response status code should be 200
    And the JSON node "data" should be null

    When I send a GET request to "/api/v2/ticket_snippets/11?include=text_snippet_content"
    Then the response status code should be 200
    And the JSON node "linked.text_snippet_content.11" should be null

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

  Scenario: I try to select chat category
    When I send a POST request to "/api/v2/ticket_snippets" with body:
    """
{
  "category": 4
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.category.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.category.errors[0].message" should be equal to "One or more of the given values is invalid."

  Scenario: I try to select category from another person
    When I send a POST request to "/api/v2/ticket_snippets" with body:
    """
{
  "category": 3
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.category.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.category.errors[0].message" should be equal to "One or more of the given values is invalid."

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
    And the JSON node "data.id" should be equal to 12
    And the JSON node "data.title" should be equal to "My Snippet"
    And the JSON node "data.person" should be equal to 1
    And the JSON node "data.shortcut_code" should be equal to "my_snippet"
    And the JSON node "data.is_draft" should be equal to 0

  Scenario: I modify text snippet
    When I send a PUT request to "/api/v2/ticket_snippets/12" with body:
    """
{
  "category": 2,
  "is_draft": 1,
  "is_global": 1
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/ticket_snippets/12"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 12
    And the JSON node "data.title" should be equal to "My Snippet"
    And the JSON node "data.person" should be equal to 0
    And the JSON node "data.shortcut_code" should be equal to "my_snippet"
    And the JSON node "data.is_draft" should be equal to 1

  Scenario: I delete ticket snippet
    When I send a DELETE request to "/api/v2/ticket_snippets/12"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/ticket_snippets/12"
    Then the response status code should be 404
