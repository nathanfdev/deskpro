@crm-nav
Feature: /*_labels endpoints
  To retrieve labels of different DeskPRO objects
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario Outline: I get labels
    When I send a GET request to "/api/v2/<target>_labels"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data[0].label" should be equal to "<label>"
    And the JSON node "data[0].color" should exist

    Examples:
      | target       | label        |
      | person       | AAA-person   |
      | organization | AAA-org      |
      | ticket       | AAA-ticket   |
      | feedback     | AAA-feedback |

  Scenario Outline: I search for labels
    When I send a GET request to "/api/v2/<target>_labels?term=BBB"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data[0].label" should contain "BBB"
    And the JSON node "data" should have 1 element

    Examples:
      | target       |
      | person       |
      | organization |
      | ticket       |
      | feedback     |
