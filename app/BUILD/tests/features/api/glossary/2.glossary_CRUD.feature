Feature: /glossary endpoint
  To CRUD DeskPRO glossary word definitions
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I retrieve a definition
    When I send a GET request to "/api/v2/glossary/1"
    And the response status code should be 200
    And the JSON node "data.definition" should be equal to "Definition Text"

  Scenario: I retrieve paginated list of definitions
    When I send a GET request to "/api/v2/glossary"
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total_pages" should be equal to 1
    And the JSON node "data" should exist
    And the JSON node "data[0].definition" should be equal to "Definition Text"

  Scenario: I create a definition
    When I send a POST request to "/api/v2/glossary" with body:
    """
{
  "definition": "Sample Definition"
}
    """
    Then the response status code should be 201
    And the header "Location" should contain "/api/v2/glossary/2"
    And the JSON node "data.definition" should be equal to "Sample Definition"

  Scenario: I create a definition with nested words
    When I send a POST request to "/api/v2/glossary" with body:
    """
{
  "definition": "Sample Definition with nested words",
  "words": [
    {"word": "First"}, {"word": "Second"}
  ]
}
    """
    Then the response status code should be 201
    And the JSON node "data.definition" should be equal to "Sample Definition with nested words"
    And the JSON node "data.words" should have 2 elements

  Scenario: I try to create a definition with empty text
    When I send a POST request to "/api/v2/glossary" with body:
    """
{
  "definition": ""
}
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.definition.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.definition.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I modify a definition
    When I send a PUT request to "/api/v2/glossary/1" with body:
    """
{
  "definition": "New Text"
}
    """
    And the response status code should be 204
    And the response should be empty

  Scenario: I modify and retrieve a definition
    When I send a PUT request to "/api/v2/glossary/1" with body:
    """
{
  "definition": "Modified"
}
    """
    And I send a GET request to "/api/v2/glossary/1"
    Then the response status code should be 200
    And the JSON node "data.definition" should be equal to "Modified"

  Scenario: I delete a definition
    When I send a DELETE request to "/api/v2/glossary/1"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I try to get not existing definition
    When I send a GET request to "/api/v2/glossary/40404"
    Then the response status code should be 404
