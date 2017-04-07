@new
Feature: /news endpoint
  To CRUD DeskPRO news by user
  As an API user
  I want an API endpoint

  Background:
    Given agent and user exist
    And I'm authenticated as user

  Scenario: I try to create a news
    Given I'm authenticated as user
    When I send a POST request to "/api/v2/news" with body:
"""
{}
"""
    And the response status code should be 403
