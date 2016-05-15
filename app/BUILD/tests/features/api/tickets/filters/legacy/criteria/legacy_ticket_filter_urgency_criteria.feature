Feature: /ticket_filters endpoint
  To check filter by urgency

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I create tickets
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Ticket 1",
  "urgency": 1,
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
  "urgency": 5,
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
  "urgency": 5,
  "fields": {
    "6": "some custom text"
  }
}
    """
    Then the response status code should be 201

  Scenario: I retrieve list of filter's tickets with additional urgency criteria
    Given I re-fill ticket search table
    When I send a GET request to "/api/v2/ticket_filters/5/tickets?urgency=5"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].urgency" should be equal to 5
    And the JSON node "data[1].urgency" should be equal to 5

    When I send a GET request to "/api/v2/ticket_filters/5/tickets?urgency=1"
    Then the response status code should be 200
    And the JSON node "data" should have 4 elements
    And the JSON node "data[0].urgency" should be equal to 1
    And the JSON node "data[1].urgency" should be equal to 1
    And the JSON node "data[2].urgency" should be equal to 1
    And the JSON node "data[3].urgency" should be equal to 1
