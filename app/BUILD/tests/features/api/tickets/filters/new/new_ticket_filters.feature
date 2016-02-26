Feature: /new/ticket_filters endpoint
  To CRUD DeskPRO tickets
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve lists of ticket filters
    When I send a GET request to "/api/v2/new/ticket_filters"
    And the response status code should be 200
    And the response should be in JSON

    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].title" should be equal to "Filter 1"
    And the JSON node "data[0].display_order" should be equal to 10
    And the JSON node "data[0].filter_set" should be equal to 1

    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].title" should be equal to "Filter 2"
    And the JSON node "data[1].display_order" should be equal to 20
    And the JSON node "data[1].filter_set" should be equal to 1

    And the JSON node "data[2].id" should be equal to 3
    And the JSON node "data[2].title" should be equal to "Filter 3"
    And the JSON node "data[2].display_order" should be equal to 30
    And the JSON node "data[2].filter_set" should be equal to 2

  Scenario: I get ticket filter
    When I send a GET request to "/api/v2/new/ticket_filters/2"
    And the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.id" should be equal to 2
    And the JSON node "data.title" should be equal to "Filter 2"
    And the JSON node "data.display_order" should be equal to 20
    And the JSON node "data.filter_set" should be equal to 1

  Scenario: I try to create a new ticket filter with empty request
    When I send a POST request to "/api/v2/new/ticket_filters"
    And the response status code should be 400
    And the response should be in JSON
    And the JSON node "errors.fields.title.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.title.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "errors.fields.term.fields.options.errors[0].code" should be equal to "term_type_does_not_exist"
    And the JSON node "errors.fields.term.fields.options.errors[0].message" should contain "You tried to create a term with the type code"
    And the JSON node "errors.fields.term.fields.options.errors[0].message" should contain "but it does not exist. Please check your term types."
