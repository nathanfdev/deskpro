@new
Feature: Brand Setup

  Background:
    Given I'm authenticated as admin

  Scenario: I get default brand
    When I send a GET request to "/api/v2/brands/default"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I can not delete the default brand
    When I send a DELETE request to "/api/v2/brands/1"
    Then the response status code should be 403

  Scenario: I create a new brand
    When I send a POST request to "/api/v2/brands" with body:
    """
{
  "name": "Test brand"
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the JSON node "data.name" should be equal to "Test brand"

  Scenario: I get all brands
    When I send a GET request to "/api/v2/brands"
    Then the response should be in JSON
    And the response status code should be 200
