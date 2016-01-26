Feature: /me/notifications endpoint
  To retrieve DeskPRO agent alerts
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I dismiss set of alerts
    When I send a POST request to "/api/v2/me/notifications/dismiss" with body:
    """
{
  "alert_ids": [1,2,3]
}
    """
    Then the response status code should be 200
    And the response should be in JSON
