@new
Feature: /me/device-setup-token endpoint

  Background:
    Given I'm authenticated as admin

  Scenario: I setup device token
    When I send a GET request to "/api/v2/me/device-setup-token"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.setup_token" should exist
    And the JSON node "data.setup_token" should contain "dp_device_setup:http"
    And the JSON node "data.setup_token" should contain "/api/v2/api_tokens/device_setup/"
