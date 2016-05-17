Feature: /task_labels endpoint
  I want to get all task labels

  Background:
    Given I install the "api" data set
    And my request is authenticated

  Scenario: Successfully create a task
    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "My test task",
  "labels": ["label1", "label2"]
}
    """
    Then the response should be in JSON
    And the response status code should be 201

    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "My test task",
  "labels": ["label3", "label4"]
}
    """
    Then the response should be in JSON
    And the response status code should be 201

  Scenario: I GET list of task labels
    When I send a GET request to "/api/v2/task_labels"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 7 elements
    And the JSON node "data[0].label_type" should be equal to "task"
    And the JSON node "data[0].label" should be equal to "AAA-task"
    And the JSON node "data[1].label" should be equal to "BBB-task"
    And the JSON node "data[2].label" should be equal to "CCC-task"
    And the JSON node "data[3].label" should be equal to "label1"
    And the JSON node "data[4].label" should be equal to "label2"
    And the JSON node "data[5].label" should be equal to "label3"
    And the JSON node "data[6].label" should be equal to "label4"
