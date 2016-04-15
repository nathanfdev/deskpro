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
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to 'The option "set_status_category" with value "anything" is invalid.'

  Scenario: I try to add incorrect labels to feedback with ID=1
    When I send a POST request to "/api/v2/mass_actions/feedback" with body:
    """
{
  "ids": [1],
  "params":{"add_labels": 1}
}
    """
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to 'The option "add_labels" with value "1" is expected to be of type "array", but is of type "string".'

  Scenario: I try to remove incorrect labels to feedback with ID=1
    When I send a POST request to "/api/v2/mass_actions/feedback" with body:
    """
{
  "ids": [1],
  "params":{"remove_labels": 1}
}
    """
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to 'The option "remove_labels" with value "1" is expected to be of type "array", but is of type "string".'

  Scenario: I apply set of actions on feedback with ID=1
    When I send a POST request to "/api/v2/mass_actions/feedback" with body:
    """
{
  "ids": [1],
  "params":{
     "set_category": "Linux",
     "set_status_category": 1,
     "set_type": 1,
     "add_labels": ["first", "second"]
  }
}
    """
    Then the response status code should be 200

  Scenario: I check if all mass actions was applied to feedback with ID=1
    When I send a GET request to "/api/v2/feedback"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].category" should be equal to 1
    And the JSON node "data[0].status_category" should be equal to 1
    And the JSON node "data[0].labels[0]" should be equal to "first"
    And the JSON node "data[0].labels[3]" should be equal to "second"


  Scenario: I apply set of actions on feedback with ID=1
    When I send a POST request to "/api/v2/mass_actions/feedback" with body:
    """
{
  "ids": [1],
  "params":{
     "set_of_actions": ["delete"],
     "remove_labels": ["label1", "second"]
  }
}
    """
    Then the response status code should be 200

  Scenario: I check if all mass actions was applied to feedback with ID=1
    When I send a GET request to "/api/v2/feedback"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].status" should be equal to "hidden"
    And the JSON node "data[0].hidden_status" should be equal to "deleted"
    And the JSON node "data[0].labels[0]" should be equal to "first"
    And the JSON node "data[0].labels[1]" should be equal to "label2"