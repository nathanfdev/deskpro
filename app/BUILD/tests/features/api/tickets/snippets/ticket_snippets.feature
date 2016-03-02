@text-snippets
Feature: /text_snippets endpoint
  To CRUD DeskPRO text snippets
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve a list of text snippets
    When I send a GET request to "/api/v2/ticket_snippets"
    And the response status code should be 200
    And print last JSON response

  Scenario: I add a new text snippet
    When I send a POST request to "/api/v2/ticket_snippets" with body:
    """
{

}
    """
    Then the response status code should be 201
