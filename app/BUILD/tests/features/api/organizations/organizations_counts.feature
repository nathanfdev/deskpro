@organization @counts @crm-nav
Feature: /organizations/counts endpoint
  To retrieve number of DeskPRO organizations
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I get number of organizations
    When I send a GET request to "/api/v2/organizations/counts"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 2
