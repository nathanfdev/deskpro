@new
Feature: Feedback brand settings Setup

  Background:
    Given I'm authenticated as admin

  Scenario: I get default brand settings
    When I send a GET request to "/api/v2/settings/brands/1/portal/feedback"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I update default brand settings
    When I send a POST request to "/api/v2/settings/brands/1/portal/feedback" with body:
    """
{
  "brand": "1",
  "enabled":true,
  "tab_enabled":false,
  "subscriptions":true
}
    """
    Then the response should be in JSON
    And the response status code should be 204


  Scenario: I check updated default brand settings
    When I send a GET request to "/api/v2/settings/brands/1/portal/feedback"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.enabled" should be true
    And the JSON node "data.tab_enabled" should be false
    And the JSON node "data.subscriptions" should be true

  Scenario: I create a new brand and update its settings
    When I send a POST request to "/api/v2/brands" with body:
    """
{
  "name": "Test brand",
  "url": "my.domain.com"
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    Then I send a POST request to "/api/v2/settings/brands/{lastCreatedId}/portal/feedback" with body:
    """
{
  "brand": "~lastCreatedId~",
  "enabled":false,
  "tab_enabled":true,
  "subscriptions":false
}
    """
    Then the response should be in JSON
    And the response status code should be 204

  Scenario: I check updated brand settings
    When I send a GET request to "/api/v2/settings/brands/{lastCreatedId}/portal/feedback"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.enabled" should be false
    And the JSON node "data.tab_enabled" should be true
    And the JSON node "data.subscriptions" should be false

  Scenario: I check that the default brand settings remain the same
    When I send a GET request to "/api/v2/settings/brands/1/portal/feedback"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.enabled" should be true
    And the JSON node "data.tab_enabled" should be false
    And the JSON node "data.subscriptions" should be true