@chat-nav @tasks-nav
Feature: /people endpoint
  To retrieve DeskPRO people
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I get paginated list of people
    When I send a GET request to "/api/v2/people"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 4 elements
    And the JSON node "data[0].name" should exist

  Scenario: I get a single person
    When I send a GET request to "/api/v2/people/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.id" should be equal to "1"
    And the JSON node "data.name" should be equal to "Link Admin"

  @basic
  Scenario: I create a person
    When I send a POST request to "/api/v2/people" with body:
    """
{
  "name": "Sample Person",
  "primary_email": "sample.person@deskpro.com",
  "organization": 1,
  "organization_position": "Chief Sample Person",
  "user_groups": [1, 2],
  "agent_groups": [7, 8],
  "fields": {
    "6": "some text"
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 5
    And the JSON node "data.name" should be equal to "Sample Person"
    And the JSON node "data.organization" should be equal to 1
    And the JSON node "data.organization_position" should be equal to "Chief Sample Person"
    And the JSON node "data.primary_email" should be equal to "sample.person@deskpro.com"
    And the JSON node "data.fields" should have 4 elements
    And the JSON node "data.fields.5.value" should exist
    And the JSON node "data.fields.6.value" should be equal to "some text"
    And the JSON node "data.fields.7.value" should be equal to 0
    And the JSON node "data.usergroups" should have 4 elements
    And the JSON node "data.usergroups[0]" should be equal to 1
    And the JSON node "data.usergroups[1]" should be equal to 2
    And the JSON node "data.usergroups[2]" should be equal to 7
    And the JSON node "data.usergroups[3]" should be equal to 8

  Scenario: I try to create a person providing empty data
    When I send a POST request to "/api/v2/people"
    Then the response should be in JSON
    And the response status code should be 400

  Scenario: I modify and retrieve a person
    When I send a PUT request to "/api/v2/people/5" with body:
    """
{
  "name": "Modified Name",
  "user_groups": [2],
  "agent_groups": [7]
}
    """
    And the response status code should be 204

    When I send a GET request to "/api/v2/people/5"
    Then the response status code should be 200
    And the JSON node "data.name" should be equal to "Modified Name"
    And the JSON node "data.usergroups" should have 2 elements
    And the JSON node "data.usergroups[0]" should be equal to 2
    And the JSON node "data.usergroups[1]" should be equal to 7

  Scenario: I modify usergroups
    When I send a PUT request to "/api/v2/people/5" with body:
    """
{
  "user_groups": [1],
  "agent_groups": [8]
}
    """
    And the response status code should be 204

    When I send a GET request to "/api/v2/people/5"
    Then the response status code should be 200
    And the JSON node "data.name" should be equal to "Modified Name"
    And the JSON node "data.usergroups" should have 2 elements
    And the JSON node "data.usergroups[0]" should be equal to 1
    And the JSON node "data.usergroups[1]" should be equal to 8

  Scenario: I reset usergroups
    When I send a PUT request to "/api/v2/people/5" with body:
    """
{
  "user_groups": [],
  "agent_groups": []
}
    """
    And the response status code should be 204

    When I send a GET request to "/api/v2/people/5"
    Then the response status code should be 200
    And the JSON node "data.name" should be equal to "Modified Name"
    And the JSON node "data.usergroups" should have 0 elements
