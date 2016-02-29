@ticket-filters
Feature: /new/ticket_filter_sets/{id}/count endpoint
  To ticket filters grouping count
  As a developer
  I want to check endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve list of all ticket filter sets counts
    When I send a GET request to "/api/v2/new/ticket_filter_sets/1/count"
    Then the response status code should be 200
    And the response should be in JSON
    And print last JSON response
