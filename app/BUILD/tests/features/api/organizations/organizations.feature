Feature: /organizations endpoint
  To CRUD DeskPRO organizations
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve list of organizations
    When I send a GET request to "/api/v2/organizations"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 2 elements

    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].name" should be equal to "Organization 1"

    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].name" should be equal to "Organization 2"

  Scenario: I try to add a new organization with empty request
    When I send a POST request to "/api/v2/organizations"
    Then the response status code should be 400
    And the response should be in JSON

    And the JSON node "errors.fields.name.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.name.errors[0].message" should be equal to "This value should not be blank."

    And the JSON node "errors.fields.importance.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.importance.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I create a new organization
    When I send a POST request to "/api/v2/organizations" with body:
    """
{
  "name": "Organization 3",
  "summary": "test organization",
  "importance": 3
}
    """
    And print last JSON response
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 3
    And the JSON node "data.name" should be equal to "Organization 3"
    And the JSON node "data.summary" should be equal to "test organization"
    And the JSON node "data.importance" should be equal to 3
