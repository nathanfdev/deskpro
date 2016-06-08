@new
Feature: /glossary/word endpoint
  To CRUD DeskPRO glossary words
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as admin
    And I have a GlossaryWordDefinition record with "definition" equal to "Test" referenced as def
    And only the following GlossaryWord records exist:
      | #  | Definition | Word        |
      | w1 | {def}      | First Word  |
      | w1 | {def}      | Second Word |

  Scenario: I retrieve a word
    When I send a GET request to "/api/v2/glossary/words/First%20Word"
    And the response status code should be 200
    And the JSON node "data.word" should be equal to "First Word"

  Scenario: I retrieve list of words
    When I send a GET request to "/api/v2/glossary/words"
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data[0].word" should be equal to "First Word"

  Scenario: I try to create a word providing empty data
    When I send a POST request to "/api/v2/glossary/words"
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.word.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.word.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "errors.fields.definition.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.definition.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I create a word
    When I send a POST request to "/api/v2/glossary/words" with body:
    """
{
  "word": "Sample Word",
  "definition": ~def~
}
    """
    Then the response status code should be 201
    And the header "Location" should be equal to "/api/v2/glossary/words/{lastCreatedId}"
    And the JSON node "data.id" should exist
    And the JSON node "data.word" should be equal to "Sample Word"

  Scenario: I modify and retrieve a word
    When I send a PUT request to "/api/v2/glossary/words/{w1}" with body:
    """
{
  "word": "Modified",
  "definition": ~def~
}
    """
    And I send a GET request to "/api/v2/glossary/words/modified"
    Then the response status code should be 200
    And the JSON node "data.word" should be equal to "Modified"

  Scenario: I delete a word
    When I send a DELETE request to "/api/v2/glossary/words/{w1}"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I try to get not existing word
    When I send a GET request to "/api/v2/glossary/words/non-existent"
    Then the response status code should be 404
