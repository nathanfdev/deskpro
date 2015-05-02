Feature: /filters endpoint error codes
  To report errors on the UI
  As a developer
  I need detailed information on term engine validation errors

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: If there is a non-valid term "type" I always get "term_type_does_not_exist" error code
    When I send a POST request to "/api/v2/filters" with body:
    """
{
}
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "status" should be equal to 400
    And the JSON node "code" should be equal to "term_type_does_not_exist"

  Scenario: If there is a non-valid term "type" I always get "term_type_does_not_exist" error code 2
    When I send a POST request to "/api/v2/filters" with body:
    """
{
  "title": "My Sales Tickets",
  "term": {
    "type": "composite",
    "op": "and",
    "terms": [
      {
        "type": "some type that does not exist"
      }
    ]
  }
}
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "status" should be equal to 400
    And the JSON node "code" should be equal to "term_type_does_not_exist"

