@new
Feature: /feedback_labels endpoint
  I want to get all feedback labels

  Background:
    Given I'm authenticated as agent

  Scenario: I GET list of feedback labels
    Given only the following "LabelDef" records exist:
    | label_type | label | color | total |
    | feedback   |  l1   | red   | 0     |
    | feedback   |  l2   | blue  | 0     |
    | feedback   |  l3   | white | 0     |

    When I send a GET request to "/api/v2/feedback_labels"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 3 elements

    And the JSON node "data[0].label_type" should be equal to "feedback"
    And the JSON node "data[0].label" should be equal to "l1"
    And the JSON node "data[1].label" should be equal to "l2"
    And the JSON node "data[2].label" should be equal to "l3"
