Feature: Production mode

  Background:
    Given I install the api data set
    And I have no logged system alert events

  Scenario: I print PHP version
    Given I have TEST_AUTH as my server info auth code
    And I go to "/__serverinfo/php_version?auth=TEST_AUTH"
    And print last response

  Scenario: I visit Portal home page
    When I go to "/"
    Then the response status code should be 200
    And there should be no system alert events

  Scenario: I retrieve people via API
    Given I log in as admin from the portal
    And I go to "/api/v2/people"
    Then the response status code should be 200
    And the JSON node "data" should exist
    And there should be no system alert events
