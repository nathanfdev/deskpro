Feature: Sandbox Widgets Example
  In order to demonstrate the API working
  As a developer
  I want a fully functional endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I create a widget
    When I send a POST request to "/api/v2/sandbox_widgets" with body:
    """
    {
      "name": "My Awesome Widget",
      "inventory": 10
    }
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/sandbox_widgets/1"
    And the JSON node "data" should exist
    And the JSON node "data.name" should be equal to "My Awesome Widget"
    And the JSON node "data.inventory" should be equal to 10

  Scenario: I GET a single widget
    When I send a GET request to "/api/v2/sandbox_widgets/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.name" should be equal to "My Awesome Widget"
    And the JSON node "data.inventory" should be equal to 10

  Scenario: I modify a widget
    When I send a PUT request to "/api/v2/sandbox_widgets/1" with body:
    """
    {
      "inventory": 4
    }
    """
    Then the response status code should be 204
    And the response should be empty

  Scenario: I GET all the widgets and see the widget 1 was updated
    When I send a GET request to "/api/v2/sandbox_widgets"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].name" should be equal to "My Awesome Widget"
    And the JSON node "data[0].inventory" should be equal to 4

  Scenario: I create a child widget
    When I send a POST request to "/api/v2/sandbox_widgets" with body:
    """
    {
      "name": "My Child Widget",
      "inventory": 3,
      "parent": 1
    }
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/sandbox_widgets/2"
    And the JSON node "data" should exist
    And the JSON node "data.name" should be equal to "My Child Widget"
    And the JSON node "data.inventory" should be equal to 3
    And the JSON node "data.parent" should be equal to 1

  Scenario: My GET request on the resource should be paged
    When I send a GET request to "/api/v2/sandbox_widgets"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 2
    And the JSON node "meta.pagination.count" should be equal to 2
    And the JSON node "meta.pagination.per_page" should be equal to 2
    And the JSON node "meta.pagination.current_page" should be equal to 1
    And the JSON node "meta.pagination.total_pages" should be equal to 1

  Scenario: I GET a widget and I side-load all associated widgets
    When I send a GET request to "/api/v2/sandbox_widgets/1?include=sandbox_widget"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "linked" should exist
    And the JSON node "linked.sandbox_widget" should have 2 elements
    And the JSON node "linked.sandbox_widget.2.id" should be equal to 2
    And the JSON node "data.children[0]" should be equal to 2
    And print last JSON response

  Scenario: I DELETE a widget
    When I send a DELETE request to "/api/v2/sandbox_widgets/2"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I fail to GET the deleted widget
    When I send a GET request to "/api/v2/sandbox_widgets/2"
    Then the response status code should be 404