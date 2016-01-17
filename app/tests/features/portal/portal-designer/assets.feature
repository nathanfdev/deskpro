Feature: Assets management

  Scenario: I get list of assets
    Given I am authenticated as admin
    When I send a GET request to "/portal/api/style/edit-theme-set/assets"
    Then the response status code should be 200
    And the JSON node "data" should exist

  # ToDo: test asset upload

  # ToDo: test delete

