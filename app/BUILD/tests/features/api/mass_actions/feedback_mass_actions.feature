Feature: /mass_actions/feedback endpoint
  To complete mass actions on feedback list
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I set incorrect hidden_status for feedback with ID=1
    When I send a POST request to "/api/v2/mass_actions/feedback" with body:
    """
{
  "ids": [1],
  "params":{"set_hidden_status": "incorrect"}
}
    """
    And print last JSON response
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to 'The option "set_hidden_status" with value "incorrect" is invalid. Accepted values are: "deleted", "draft", "spam", "unpublished".'

  Scenario: I set incorrect status category for feedback with ID=1
    When I send a POST request to "/api/v2/mass_actions/feedback" with body:
    """
{
  "ids": [1],
  "params":{"set_status_category": 1000}
}
    """
    And print last JSON response
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to "Status category with ID=1000 doesn't exists"

  Scenario: I set incorrect status category for feedback with ID=1
    When I send a POST request to "/api/v2/mass_actions/feedback" with body:
    """
{
  "ids": [1],
  "params":{"set_status_category": [1,2,3]}
}
    """
    And print last JSON response
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to 'The option "set_status_category" with value array is expected to be of type "string" or "int", but is of type "array".'


  Scenario: I set incorrect status category for feedback with ID=1
    When I send a POST request to "/api/v2/mass_actions/feedback" with body:
    """
{
  "ids": [1],
  "params":{"set_status_category": "anything"}
}
    """
    And print last JSON response
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to 'The option "set_status_category" with value "anything" is invalid.'

