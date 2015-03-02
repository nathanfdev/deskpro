Feature: Basic API Response
  In order to interact with the API
  As a developer
  I need to see the basic usage examples working

  Background: Using a empty data set
    Given I install the empty data set

  Scenario: I do a simple GET
    When I send a GET request to "/api/v2/sandbox"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: Request with a non-JSON body
    When I send a POST request to "/api/v2/sandbox" with body:
        """
        foo=bar
        """
    Then the JSON node "code" should be equal to 400
    And the JSON node "message" should exist
    And the response status code should be 400
