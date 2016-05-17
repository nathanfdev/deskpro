Feature: /ticket_filters endpoint
  To check filter by agent

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I create tickets
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Ticket 1",
  "agent":  2,
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
  "agent":  2,
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
  "agent":  1,
  "fields": {
    "6": "some custom text"
  }
}
    """
    Then the response status code should be 201

  Scenario: I retrieve list of filter's tickets with additional agent criteria
    Given I re-fill ticket search table
    When I send a GET request to "/api/v2/ticket_filters/5/tickets?agent=1"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].agent" should be equal to 1
    And the JSON node "data[1].agent" should be equal to 1
    And the JSON node "data[2].agent" should be equal to 1

    When I send a GET request to "/api/v2/ticket_filters/5/tickets?agent=2"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].agent" should be equal to 2
    And the JSON node "data[1].agent" should be equal to 2
    And the JSON node "data[2].agent" should be equal to 2
