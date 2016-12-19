@new
Feature: /task_labels endpoint
  I want to get all task labels

  Background:
    Given I'm authenticated as "admin"

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



  Scenario: I add new labels to task and they appear when I GET list of task labels
    Given no "LabelTask" records exist
    And no labels with type "task" exist
    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "My test task",
  "labels": ["label3", "label4"]
}
    """
    Then the response should be in JSON
    And the response status code should be 201

    When I send a GET request to "/api/v2/task_labels"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].label_type" should be equal to "task"
    And the JSON node "data[0].label" should be equal to "label3"
    And the JSON node "data[1].label" should be equal to "label4"
