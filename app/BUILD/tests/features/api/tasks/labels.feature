@tasks-nav
Feature: /task_labels endpoint
  To CRUD DeskPRO task labels
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: Successfully create a label
    When I send a POST request to "/api/v2/task_labels" with body:
    """
{
  "label": "test",
  "task": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    # And the header "Location" should be equal to "/api/v2/task_labels/1"
    # should be returned when https://trello.com/c/0q0iVrS9/599-gathered-from-code-add-location-header-in-crud-post is done
    And the JSON node "data" should exist
    And the JSON node "data.label" should be equal to "test"

  Scenario: I GET a single label
    When I send a GET request to "/api/v2/task_labels/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.label" should be equal to "test"

  Scenario: I GET labels
    When I send a GET request to "/api/v2/task_labels"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination.count" should be equal to 1
    And the JSON node "meta.pagination.current_page" should be equal to 1
    And the JSON node "meta.pagination.total_pages" should be equal to 1
    And the JSON node "meta.pagination.total" should be equal to 1
    And the JSON node "data" should exist
    And the JSON node "data[0].label" should be equal to "test"

  Scenario: I modify a label
    When I send a PUT request to "/api/v2/task_labels/1" with body:
    """
{
  "label": "modified"
}
    """
    Then the response status code should be 204
    And the response should be empty

  Scenario: I verify the resource has been updated by the PUT request
    When I send a GET request to "/api/v2/task_labels/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.label" should be equal to "modified"

  Scenario: I try to POST a bad label without a parent task
    When I send a POST request to "/api/v2/task_labels" with body:
    """
{
  "label": "broken"
}
    """
    Then the response should be in JSON
    And the response status code should be 400

  Scenario: I DELETE a single task
    When I send a DELETE request to "/api/v2/task_labels/1"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I verify the resource has been removed by the DELETE request
    When I send a GET request to "/api/v2/task_labels/1"
    Then the response should be in JSON
    And the response status code should be 404