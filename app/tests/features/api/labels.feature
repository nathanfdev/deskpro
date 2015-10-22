@crm-nav
Feature: /*_labels endpoints
  To retrieve labels of different DeskPRO objects
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario Outline: I get person labels
    When I send a GET request to "/api/v2/<target>_labels"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data[0].label" should be equal to "<label>"
    And the JSON node "data[0].color" should exist

    Examples:
      | target       | label                 |
      | person       | person label #1       |
      | organization | organization label #1 |
      | ticket       | ticket label #1       |
      | feedback     | bar                   |
