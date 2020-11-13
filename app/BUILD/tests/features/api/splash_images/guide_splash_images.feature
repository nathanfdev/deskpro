@new
Feature: /api/v2/guides/{id}/splash_image_upload
  To upload splash images to guide

  Background:
    Given I'm authenticated as admin
    And I have only default brand
    And only the following "Guide" records exist:
      | #  | Title      | Slug         | Description  | brand          |
      | t1 | Test Guide | test-guide   | Test         | {defaultBrand} |
    And I attach image file to my request

  Scenario: I upload a splash image
    When I send a POST request to "/api/v2/guides/{t1}/splash_image_upload" with "DummyImage"
    Then the response should be in JSON
