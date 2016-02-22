@chat-nav @tasks-nav
Feature: /people endpoint
  To retrieve DeskPRO people
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

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
  "organization_position": "Chief Sample Person"
}
    """
    Then the response status code should be 201
    And the JSON node "data.name" should be equal to "Sample Person"
    And the JSON node "data.organization_position" should be equal to "Chief Sample Person"
    And the JSON node "data.primary_email" should be equal to "sample.person@deskpro.com"

  Scenario: I try to create a person providing empty data
    When I send a POST request to "/api/v2/people" with body:
    """
{
}
    """
    Then the response should be in JSON
    And the response status code should be 400

  Scenario: I modify and retrieve a person
    Given I have a person with name "Sample Person" and primary email "sample.person@gmail.com"
    When I send a PUT request to the just created person resource:
    """
{
  "name": "Modified Name"
}
    """
    And the response status code should be 204

    And I retrieve the person data
    Then the response status code should be 200
    And the JSON node "data.name" should be equal to "Modified Name"
