Feature: Custom logo

  Background: Fresh database
    Given I install the fresh data set

  Scenario: I get custom logo data
    Given I am authenticated as admin
    When I send a GET request to "/portal/api/style/edit-theme-set/logo"
    Then the response status code should be 200
    And the JSON node "data" should exist

  Scenario: I upload custom logo
    Given I am authenticated as admin
    When I send the "image.png" file as "file" to "/portal/api/style/edit-theme-set/logo"
    Then the response status code should be 200

  Scenario: I upload logo and retrieve its' URL
    Given I am authenticated as admin
    And I send the "image.png" file as "file" to "/portal/api/style/edit-theme-set/logo"
    When I send a GET request to "/portal/api/style/edit-theme-set/logo"
    Then the JSON node "data.url" should exist

  Scenario: I delete logo
    Given I am authenticated as admin
    And I send the "image.png" file as "file" to "/portal/api/style/edit-theme-set/logo"
    And I send a DELETE request to "/portal/api/style/edit-theme-set/logo"
    When I send a GET request to "/portal/api/style/edit-theme-set/logo"
    Then the JSON node "data.url" should not exist
