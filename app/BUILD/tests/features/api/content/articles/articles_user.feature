@new
Feature: /articles endpoint
  To operation with DeskPRO articles by user
  As an API user
  I want an API endpoint

  Background:
    Given agent and user exist
    Given I'm authenticated as user

  Scenario: I try to create an article as user
    When I send a POST request to "/api/v2/articles" with body:
"""
{}
"""
    And the response status code should be 403
