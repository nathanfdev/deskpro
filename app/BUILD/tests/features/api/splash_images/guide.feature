@new
Feature: /api/v2/topics/{id}/splash_image_upload
  To upload splash images

  Background:
    Given I'm authenticated as admin
    And only the following "Guides" records exist:
      | #  | Title      | Slug         | Content      |
      | t1 | Test Guide | test-guide   | Test         |
    And I attach image file to my request

  Scenario: I upload a splash image
    When I send a POST request to "/api/v2/topics/{t1}/splash_image_upload" with "DummyImage"
    Then the response status code should be 200
