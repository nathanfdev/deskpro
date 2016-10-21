@new
Feature: /voice_assets endpoint

  Background:
    Given I'm authenticated as admin

  Scenario: I create text asset
    When I send a POST request to "/api/v2/voice_assets" with body:
    """
{
  "name": "My asset",
  "type": "text",
  "text": "my text",
  "language": "en-GB"
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should exist
    And the JSON node "data.name" should be equal to the string "My asset"
    And the JSON node "data.type" should be equal to the string "text"
    And the JSON node "data.text" should be equal to the string "my text"
    And the JSON node "data.language" should be equal to the string "en-GB"
