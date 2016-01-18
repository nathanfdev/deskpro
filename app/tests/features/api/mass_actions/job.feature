Feature: /mass_actions endpoint
  To complete mass actions on item's lists
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @basic
  Scenario: I create first job
    When I send a POST request to "/api/v2/mass_actions/" with body:
    """
{
  "jobType": "publish_mass",
  "params":{
    "ids":["1"],
    "content":"feedback_comments",
    "actions":{"delete":"1"}
  }
}
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON node "job" should be equal to "1"

  Scenario: I get first job data
    When I send a GET request to "/api/v2/mass_actions/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.id" should be equal to "1"
    And the JSON node "data.type" should be equal to "publish_mass"
