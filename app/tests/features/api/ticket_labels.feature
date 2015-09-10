@tickets-nav
Feature: /ticket_labels endpoint
  To retrieve info on ticket labels
  As a developer
  I want an endpoint for ticket labels

  Background:
    Given I install the "api" data set
    And my request is authenticated

  @reinstall
  Scenario: I GET all ticket labels
    When I send a GET request to "/api/v2/ticket_labels"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data[0]" should be equal to "bar"
    And the JSON node "data[1]" should be equal to "foo"
    And the JSON node "meta.count" should be equal to "2"
