@new
Feature: Portal Api Authorization

  Scenario: I get widget session
    Given the setting "core.site_name" is set to "My helpdesk"
    When I send a POST request to "/portal/api/auth/session"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.person" should exist
    And the JSON node "data.global_settings.company.name" should be equal to "My helpdesk"
