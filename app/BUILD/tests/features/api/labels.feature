Feature: /*_labels endpoints
  To retrieve labels of different DeskPRO objects
  As an API user
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
      | task         | AAA-task     |

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
      | task         |

  Scenario Outline: I try to add duplicate labels
    When I send a POST request to "/api/v2/<endpoint>" with body:
    """
{
  "labels": ["label1", "label1", "label2"]
}
    """
    And the response status code should be 400
    And the JSON node "errors.fields.labels.errors[0].code" should be equal to "not_unique_collection"
    And the JSON node "errors.fields.labels.errors[0].message" should be equal to "One or more of the given values is not unique."

    When I send a PUT request to "/api/v2/<endpoint>/1" with body:
    """
{
  "labels": ["label1", "label2", "label1"]
}
    """
    And the response status code should be 400
    And the JSON node "errors.fields.labels.errors[0].code" should be equal to "not_unique_collection"
    And the JSON node "errors.fields.labels.errors[0].message" should be equal to "One or more of the given values is not unique."


    Examples:
      | endpoint      |
      | people        |
      | organizations |
      | tickets       |
      | tasks         |
