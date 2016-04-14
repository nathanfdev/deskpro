@basic
Feature: Production mode

  Background:
    Given I have no logged system alert events

  Scenario: I visit Portal home page
    And I go to "/"
    Then the response status code should be 200
    And there should be no system alert events

  Scenario: I retrieve people via API
    Given I log in as agent from the portal
    And I go to "/api/v2/people"
    Then the response status code should be 200
    And the JSON node "data" should exist
    And there should be no system alert events
