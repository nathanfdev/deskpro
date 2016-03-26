Feature: Discover settings

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I get settings
    When I send a GET request to "/api/v2/helpdesk/discover"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.is_deskpro" should be equal to 1
    And the JSON node "data.helpdesk_url" should exist
    And the JSON node "data.base_api_url" should exist
    And the JSON node "data.build" should exist
