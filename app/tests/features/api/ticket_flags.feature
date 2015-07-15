Feature: /ticket_flags endpoint
  To retrieve info on ticket flags
  As a developer
  I want an endpoint for ticket flags

  Background:
    Given I install the "api" data set
    And my request is authenticated

  @reinstall
  Scenario: I GET all ticket flags
    When I send a GET request to "/api/v2/ticket_flags"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data[0]" should be equal to "blue"
    And the JSON node "meta.count" should be equal to "7"

 Scenario: I GET ticket flags count
    When I send a GET request to "/api/v2/ticket_flags/green/count"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.count" should be equal to "2"
