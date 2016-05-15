Feature: /ticket_filters endpoint
  To check filter by language

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I create tickets
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Ticket 1",
  "language":  1,
  "agent": 1,
  "fields": {
    "6": "some custom text"
  }
}
    """
    Then the response status code should be 201

    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Ticket 2",
  "language":  2,
  "agent": 1,
  "fields": {
    "6": "some custom text"
  }
}
    """
    Then the response status code should be 201

    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Ticket 3",
  "language":  1,
  "agent": 1,
  "fields": {
    "6": "some custom text"
  }
}
    """
    Then the response status code should be 201

  Scenario: I retrieve list of filter's tickets with additional language criteria
    Given I re-fill ticket search table
    When I send a GET request to "/api/v2/ticket_filters/1/tickets?language=1"
    Then the response status code should be 200
    And the JSON node "data" should have 4 elements
    And the JSON node "data[0].language" should be equal to 1
    And the JSON node "data[1].language" should be equal to 0
    And the JSON node "data[2].language" should be equal to 1
    And the JSON node "data[3].language" should be equal to 1

    When I send a GET request to "/api/v2/ticket_filters/1/tickets?language=2"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].language" should be equal to 2
