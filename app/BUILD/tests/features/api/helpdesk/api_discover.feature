Feature: API discover feature
  Used to discover Deskpro API

  Scenario: I am discovering helpdesk API
    When I send a GET request to "/api/v2/helpdesk/discover"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node data should exist
    And the JSON node "data.is_deskpro" should be equal to true
    And the JSON node "data.helpdesk_url" should exist
    And the JSON node "data.base_api_url" should exist
    And the JSON node "data.build" should exist

  Scenario: I am discovering helpdesk settings and features
    Given my request is authenticated
    When I send a GET request to "/api/v2/helpdesk/agent-client/info"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.settings" should exist
    And the JSON node "data.settings.helpdesk_name" should exist
    And the JSON node "data.settings.multi_lang" should exist
    And the JSON node "data.settings.brands" should exist
    And the JSON node "data.tickets" should exist
    And the JSON node "data.tickets.enabled" should exist
    And the JSON node "data.chat" should exist
    And the JSON node "data.chat.enabled" should exist
    And the JSON node "data.crm" should exist
    And the JSON node "data.crm.enabled" should exist
    And the JSON node "data.feedback" should exist
    And the JSON node "data.feedback.enabled" should exist
    And the JSON node "data.publish" should exist
    And the JSON node "data.publish.enabled" should exist
    And the JSON node "data.tasks" should exist
    And the JSON node "data.tasks.enabled" should exist
