@text-snippets
Feature: /text_snippet_categories endpoint
  To CRUD DeskPRO text snippet categories
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve a list of text snippet categories
    When I send a GET request to "/api/v2/text_snippet_categories"
    And the response status code should be 200
    And print last JSON response

  Scenario: I create a new snippet category
    When I send a POST request to "/api/v2/text_snippet_categories" with body:
    """
{
  "typename": "tickets"
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.typename" should be equal to "tickets"
    And the JSON node "data.person" should be equal to 0
    And the JSON node "data.is_global" should be equal to 0


