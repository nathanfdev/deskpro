@ticket-filters
Feature: /ticket_filters endpoint
  To check filter by person

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I create tickets
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Ticket 1",
  "person":  1,
  "agent": 1
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 5

    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Ticket 2",
  "person":  2,
  "agent": 1
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 6

    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Ticket 3",
  "person":  1,
  "agent": 1
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 7

  Scenario: I retrieve list of filter's tickets with additional person criteria
    Given I re-fill ticket search table
    When I send a GET request to "/api/v2/ticket_filters/1/tickets?person=1"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to 5
    And the JSON node "data[0].person" should be equal to 1
    And the JSON node "data[1].id" should be equal to 7
    And the JSON node "data[1].person" should be equal to 1

    When I send a GET request to "/api/v2/ticket_filters/1/tickets?person=2"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 6
    And the JSON node "data[0].person" should be equal to 2
