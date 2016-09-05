@new
Feature: Downloads brand settings Setup

  Background:
    Given I'm authenticated as admin
    And no Brand records exist
    And I have only default brand

  Scenario: I get default brand settings
    When I send a GET request to "/api/v2/settings/brands/{defaultBrandId}/portal/downloads"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I update default brand settings
    When I send a POST request to "/api/v2/settings/brands/{defaultBrandId}/portal/downloads" with body:
    """
{
  "enabled":true,
  "tab_enabled":false,
  "subscriptions":true
}
    """
    Then the response should be in JSON
    And the response status code should be 204

    When I send a GET request to "/api/v2/settings/brands/{defaultBrandId}/portal/downloads"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.enabled" should be true
    And the JSON node "data.tab_enabled" should be false
    And the JSON node "data.subscriptions" should be true

    When I send a POST request to "/api/v2/brands" with body:
    """
{
  "name": "Test brand",
  "url": "my.domain.com"
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    Then I send a POST request to "/api/v2/settings/brands/{lastCreatedId}/portal/downloads" with body:
    """
{
  "enabled":false,
  "tab_enabled":true,
  "subscriptions":false
}
    """
    Then the response should be in JSON
    And the response status code should be 204

    When I send a GET request to "/api/v2/settings/brands/{lastCreatedId}/portal/downloads"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.enabled" should be false
    And the JSON node "data.tab_enabled" should be true
    And the JSON node "data.subscriptions" should be false

    When I send a GET request to "/api/v2/settings/brands/{defaultBrandId}/portal/downloads"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.enabled" should be true
    And the JSON node "data.tab_enabled" should be false
    And the JSON node "data.subscriptions" should be true
