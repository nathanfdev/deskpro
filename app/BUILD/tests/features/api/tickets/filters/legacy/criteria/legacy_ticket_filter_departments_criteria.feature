@ticket-filters
Feature: /ticket_filters endpoint
  To check filter by department

  Background:
    Given I install the api data set
    And my request is authenticated
    And I re-fill ticket search table

  @reinstall
  Scenario: I retrieve list of filter's tickets with additional department criteria
    When I send a GET request to "/api/v2/ticket_filters/1/tickets?department=1"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].department" should be equal to 1
