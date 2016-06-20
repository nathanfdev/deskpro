Feature: Assets management

  Background: Fresh database
    Given I install the fresh data set

  Scenario: I upload an asset
    Given I'm authenticated as admin
    When I send the "image.png" file as "file" to "/portal/api/style/edit-theme-set/assets"
    And the JSON node "data.url" should exist

  Scenario: I get list of assets
    Given I'm authenticated as admin
    And there are no custom portal assets
    And I send the "image.png" file as "file" to "/portal/api/style/edit-theme-set/assets"
    And I send the "image2.png" file as "file" to "/portal/api/style/edit-theme-set/assets"
    When I send a GET request to "/portal/api/style/edit-theme-set/assets"
    Then the response status code should be 200

    And the JSON node "data" should have 2 elements

  Scenario: I delete an asset
    Given I'm authenticated as admin
    And there are no custom portal assets
    And I send the "image.png" file as "file" to "/portal/api/style/edit-theme-set/assets"
    When I send a DELETE request to the just uploaded custom portal asset
    Then the response status code should be 200

  Scenario: I upload 2 assets, delete 1 and retrieve collection of assets
    Given I'm authenticated as admin
    And there are no custom portal assets
    And I send the "image.png" file as "file" to "/portal/api/style/edit-theme-set/assets"
    And I send the "image2.png" file as "file" to "/portal/api/style/edit-theme-set/assets"
    When I send a DELETE request to the just uploaded custom portal asset
    And I send a GET request to "/portal/api/style/edit-theme-set/assets"
    Then the JSON node "data" should have 1 element
