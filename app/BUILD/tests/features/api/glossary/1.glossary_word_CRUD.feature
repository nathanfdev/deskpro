Feature: /glossary/word endpoint
  To CRUD DeskPRO glossary words
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I retrieve a word
    When I send a GET request to "/api/v2/glossary/words/Word%201"
    And the response status code should be 200
    And the JSON node "data.word" should be equal to "Word 1"

  Scenario: I retrieve list of words
    When I send a GET request to "/api/v2/glossary/words"
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data[0].word" should be equal to "Word 1"

  Scenario: I create a word
    When I send a POST request to "/api/v2/glossary/words" with body:
    """
{
  "word": "Sample Word",
  "definition": 1
}
    """
    Then the response status code should be 201
#    And the header "Location" should match "\/api\/v2\/glossary\/words\/\d+"
    And the JSON node "data.word" should be equal to "Sample Word"

  Scenario: I try to create a word providing empty data
    When I send a POST request to "/api/v2/glossary/words" with body:
    """
{
}
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.word" should exist

  Scenario: I modify a word
    When I send a PUT request to "/api/v2/glossary/words/1" with body:
    """
{
  "word": "New Text",
  "definition": 1
}
    """
    Then print last response
    And the response status code should be 204
    And the response should be empty

  Scenario: I modify and retrieve a word
    When I send a PUT request to "/api/v2/glossary/words/1" with body:
    """
{
  "word": "Modified",
  "definition": 1
}
    """
    And I send a GET request to "/api/v2/glossary/words/modified"
    Then the response status code should be 200
    And the JSON node "data.word" should be equal to "Modified"

  Scenario: I delete a word
    When I send a DELETE request to "/api/v2/glossary/words/1"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I try to get not existing word
    When I send a GET request to "/api/v2/glossary/words/non-existent"
    Then the response status code should be 404
