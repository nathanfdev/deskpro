Feature: Custom logo

  Scenario: I get custom logo data
    Given I am authenticated as admin
    When I send a GET request to "/portal/api/style/edit-theme-set/logo"
    Then the response status code should be 200
    And the JSON node "data" should exist

  # ToDo: test logo upload

  # ToDo: test logo delete

