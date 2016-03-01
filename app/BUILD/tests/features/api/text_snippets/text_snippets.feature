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
    When I send a GET request to "/api/v2/text_snippets"
    And the response status code should be 200
    And print last JSON response
