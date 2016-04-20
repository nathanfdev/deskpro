@tasks
Feature: /tasks endpoint
  To CRUD DeskPRO tasks
  As a developer
  I want to check list filters

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I prepare tasks
    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "Task with no labels 1"
}
    """
    Then the response should be in JSON
    And the response status code should be 201

    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "Task with no labels 2"
}
    """
    Then the response should be in JSON
    And the response status code should be 201

    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "Task with one label 1",
  "labels": ["filter_label1"]
}
    """
    Then the response should be in JSON
    And the response status code should be 201

    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "Task with one label 2",
  "labels": ["filter_label2"]
}
    """
    Then the response should be in JSON
    And the response status code should be 201

    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "Task with two labels",
  "labels": ["filter_label1", "filter_label2"]
}
    """
    Then the response should be in JSON
    And the response status code should be 201

  Scenario: I filter tasks w/o labels
    When I send a GET request to "/api/v2/tasks?no_labels=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 4 elements

    And the JSON node "data[0].title" should be equal to "Task with no labels 2"
    And the JSON node "data[0].labels" should have 0 elements

    And the JSON node "data[1].title" should be equal to "Task with no labels 1"
    And the JSON node "data[1].labels" should have 0 elements

    And the JSON node "data[2].title" should be equal to "An unassigned task"
    And the JSON node "data[2].labels" should have 0 elements

    And the JSON node "data[3].title" should be equal to "A demo task"
    And the JSON node "data[3].labels" should have 0 elements

  Scenario: I filter by one label
    When I send a GET request to "/api/v2/tasks?label=filter_label1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 2 elements

    And the JSON node "data[0].title" should be equal to "Task with two labels"
    And the JSON node "data[0].labels" should have 2 elements
    And the JSON node "data[0].labels[0]" should be equal to "filter_label1"
    And the JSON node "data[0].labels[1]" should be equal to "filter_label2"

    And the JSON node "data[1].title" should be equal to "Task with one label 1"
    And the JSON node "data[1].labels" should have 1 element
    And the JSON node "data[1].labels[0]" should be equal to "filter_label1"

  Scenario: I filter by any label
    When I send a GET request to "/api/v2/tasks?label[0]=filter_label1&label[1]=filter_label2"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 3 elements

    And the JSON node "data[0].title" should be equal to "Task with two labels"
    And the JSON node "data[0].labels" should have 2 elements
    And the JSON node "data[0].labels[0]" should be equal to "filter_label1"
    And the JSON node "data[0].labels[1]" should be equal to "filter_label2"

    And the JSON node "data[1].title" should be equal to "Task with one label 2"
    And the JSON node "data[1].labels" should have 1 element
    And the JSON node "data[1].labels[0]" should be equal to "filter_label2"

    And the JSON node "data[2].title" should be equal to "Task with one label 1"
    And the JSON node "data[2].labels" should have 1 element
    And the JSON node "data[2].labels[0]" should be equal to "filter_label1"

  Scenario: I filter by all labels
    When I send a GET request to "/api/v2/tasks?label[0]=filter_label1&label[1]=filter_label2&labels_mode=all"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 element

    And the JSON node "data[0].title" should be equal to "Task with two labels"
    And the JSON node "data[0].labels" should have 2 elements
    And the JSON node "data[0].labels[0]" should be equal to "filter_label1"
    And the JSON node "data[0].labels[1]" should be equal to "filter_label2"
