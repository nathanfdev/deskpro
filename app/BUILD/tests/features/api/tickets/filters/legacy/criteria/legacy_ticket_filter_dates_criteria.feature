Feature: /ticket_filters endpoint
  To check filter by dates

  Background:
    Given I install the api data set
    And my request is authenticated
    And I re-fill ticket search table

  Scenario: I retrieve list of filter's tickets with additional dates criteria
    When I send a GET request to "/api/v2/ticket_filters/1/tickets?waiting_time=1893456000"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/ticket_filters/1/tickets?all_waiting_time=1893456000"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/ticket_filters/1/tickets?date_created=1893456000"
    Then the response status code should be 200
