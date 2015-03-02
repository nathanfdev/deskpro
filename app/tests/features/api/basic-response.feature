Feature: Basic API Response
  In order to fetch information from the API
  As a developer
  It needs to respond according to the spec

  Scenario: I do a simple GET
    Given I send a GET request to "/api/v2/ticket_filters"
    Then the response should be in JSON
    And the response status code should be 200
