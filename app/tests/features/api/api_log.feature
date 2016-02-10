Feature: Api should log any request

  Background:
    Given I install the api data set
    And I have enabled api log feature
    And my request is authenticated

  Scenario: I send some request to API
    When I send a GET request to "/api/v2/notify/heartbeat?include_headers=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node data should exist
    And the JSON node headers should exist
    And api log should appear in table
