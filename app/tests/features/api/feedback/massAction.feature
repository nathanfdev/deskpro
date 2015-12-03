@counts @feedback-nav
Feature: /feedback/mass_action endpoint
  To update set of feedback
  As a developer
  I want an endpoint for feedback mass action

  Background:
    Given I install the "api" data set
    And my request is authenticated

  @reinstall
  Scenario: I modify a set of feedback
    When I send a PUT request to "/api/v2/feedback/mass_action?id[]=63&id[]=64" with body:
"""
{
  "category": 3
}
    """
    Then the response status code should be 204
    And the response should be empty
