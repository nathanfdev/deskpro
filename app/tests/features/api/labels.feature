@crm-nav
Feature: /*_labels endpoints
  To retrieve labels of different DeskPRO objects
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I get person labels
    When I send a GET request to "/api/v2/person_labels"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[1]" should be equal to "person label #2"

  Scenario: I get organization labels
    When I send a GET request to "/api/v2/organization_labels"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0]" should be equal to "organization label #1"
