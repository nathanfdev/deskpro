Feature: /person_custom_fields endpoint
  To retrieve DeskPRO person custom fields
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve a list of custom fields
    When I send a GET request to "/api/v2/person_custom_fields"
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "data" should have 5 elements
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].title" should be equal to "Desired Sizes"
    And the JSON node "data[0].description" should be equal to "A custom  field"
    And the JSON node "data[0].parent" should be equal to 0
    And the JSON node "data[0].children" should have 3 elements
    And the JSON node "data[0].children[0]" should be equal to 2
    And the JSON node "data[0].children[1]" should be equal to 3
    And the JSON node "data[0].children[2]" should be equal to 4

    And the JSON node "data[1].id" should be equal to 5
    And the JSON node "data[1].title" should be equal to "Delivery Time"
    And the JSON node "data[1].description" should be equal to "A custom  field"
    And the JSON node "data[1].parent" should be equal to 0
    And the JSON node "data[1].children" should have 0 elements

    And the JSON node "data[2].id" should be equal to 6
    And the JSON node "data[2].title" should be equal to "Widget Type"
    And the JSON node "data[2].description" should be equal to "A custom  field"
    And the JSON node "data[2].parent" should be equal to 0
    And the JSON node "data[2].children" should have 0 elements

    And the JSON node "data[3].id" should be equal to 7
    And the JSON node "data[3].title" should be equal to "Widget Description"
    And the JSON node "data[3].description" should be equal to "A custom  field"
    And the JSON node "data[3].parent" should be equal to 0
    And the JSON node "data[3].children" should have 0 elements

    And the JSON node "data[4].id" should be equal to 8
    And the JSON node "data[4].title" should be equal to "Multiple choice"
    And the JSON node "data[4].description" should be equal to "A custom  field"
    And the JSON node "data[4].options.multiple" should be equal to 1
    And the JSON node "data[4].options.expanded" should be equal to 1
    And the JSON node "data[4].parent" should be equal to 0
    And the JSON node "data[4].children" should have 3 elements
    And the JSON node "data[4].children[0]" should be equal to 9
    And the JSON node "data[4].children[1]" should be equal to 10
    And the JSON node "data[4].children[2]" should be equal to 11

  Scenario: I get child field
    When I send a GET request to "/api/v2/person_custom_fields/2"
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "data.id" should be equal to 2
    And the JSON node "data.title" should be equal to "Small"
    And the JSON node "data.description" should be equal to 0
    And the JSON node "data.parent" should be equal to 1
    And the JSON node "data.children" should have 0 elements
