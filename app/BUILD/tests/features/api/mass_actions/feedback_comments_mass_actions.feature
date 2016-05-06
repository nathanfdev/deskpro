Feature: /mass_actions/feedback_comments endpoint
  To complete mass actions on feedback list
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I get feedback comments and check if all mass actions was applied
    When I send a GET request to "/api/v2/feedback_comments"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].status" should be equal to "validating"

  Scenario: I approve feedback comment with ID=1
    When I send a POST request to "/api/v2/mass_actions/feedback_comments" with body:
    """
{
  "ids": [1],
  "params":{
     "set_of_actions": ["approve"]
  }
}
    """
    Then the response status code should be 200

  Scenario: I get feedback comments and check if all mass actions was applied
    When I send a GET request to "/api/v2/feedback_comments"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].status" should be equal to "visible"