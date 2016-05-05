@ticket-filters
Feature: /ticket_filters endpoint
  To check filter by custom field

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I create tickets
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Ticket 1",
  "fields": {
    "8": ["10", "11"]
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 5

    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Ticket 2",
  "fields": {
    "8": ["9"]
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 6

    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Ticket 3",
  "fields": {
    "8": ["10"]
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 7

  Scenario: I retrieve list of filter's tickets with additional custom field criteria
    Given I re-fill ticket search table
    When I send a GET request to "/api/v2/ticket_filters/5/tickets?ticket_field.8=10"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to 5
    And the JSON node "data[0].fields.8.value[0]" should be equal to 10
    And the JSON node "data[1].id" should be equal to 7
    And the JSON node "data[1].fields.8.value[0]" should be equal to 10

    When I send a GET request to "/api/v2/ticket_filters/5/tickets?ticket_field.8[]=10&ticket_field.8[]=9"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to 5
    And the JSON node "data[0].fields.8.value[0]" should be equal to 10
    And the JSON node "data[1].id" should be equal to 6
    And the JSON node "data[1].fields.8.value[0]" should be equal to 9
    And the JSON node "data[2].id" should be equal to 7
    And the JSON node "data[2].fields.8.value[0]" should be equal to 10
