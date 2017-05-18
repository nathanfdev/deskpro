@new
Feature: /articles endpoint
  To CRUD DeskPRO downloads by user
  As an API user
  I want an API endpoint

  Background:
    Given agent and user exist
    And I'm authenticated as user

  Scenario: I try to create a download as user
    When I send a POST request to "/api/v2/articles"
    And the response status code should be 403
