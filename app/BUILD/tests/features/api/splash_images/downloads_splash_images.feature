@new
Feature: /api/v2/articles/{id}/splash_image_upload
  To upload splash images to article

  Background:
    Given I'm authenticated as admin
    And I have only default brand
    And the following Guide records exist:
      | #  | Title      | Slug         | Description  | brand          |
      | t1 | Test Guide | test-guide   | Test         | {defaultBrand} |
    And I attach image file to my request

  Scenario: I upload a splash image
    When I send a POST request to "/api/v2/articles/{t1}/splash_image_upload" with "DummyImage"
    Then the response status code should be 200
