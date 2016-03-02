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
    And the JSON node "data" should have 4 elements

    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].category" should be equal to 1
    And the JSON node "data[0].shortcut_code" should be equal to "ticket_snippet1"
    And the JSON node "data[0].is_draft" should be equal to 1

    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].category" should be equal to 1
    And the JSON node "data[1].shortcut_code" should be equal to "ticket_snippet2"
    And the JSON node "data[1].is_draft" should be equal to 1

    And the JSON node "data[2].id" should be equal to 3
    And the JSON node "data[2].category" should be equal to 2
    And the JSON node "data[2].shortcut_code" should be equal to "ticket_snippet3"
    And the JSON node "data[2].is_draft" should be equal to 0

    And the JSON node "data[3].id" should be equal to 5
    And the JSON node "data[3].category" should be equal to 3
    And the JSON node "data[3].shortcut_code" should be equal to "ticket_snippet5"
    And the JSON node "data[3].is_draft" should be equal to 1

  Scenario: I add a new text snippet
    When I send a POST request to "/api/v2/ticket_snippets" with body:
    """
{

}
    """
    Then the response status code should be 201
