@basic
Feature: /slas endpoint
  To retrieve DeskPRO SLAs
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I get paginated list of SLAs
    When I send a GET request to "/api/v2/slas"
    Then the response should be in JSON
    And the response status code should be 200
    And print last JSON response

  Scenario: I get a single person
    When I send a GET request to "/api/v2/slas/1"
    Then the response should be in JSON
    And the response status code should be 200
    And print last JSON response
