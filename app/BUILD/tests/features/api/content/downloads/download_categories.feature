@new
Feature: /download_categories endpoint
  To CRUD DeskPRO download categories
  As an API user
  I want an API endpoint

  Background:
    Given agent and user exist
    And I'm authenticated as agent
    And I have permissions to use Downloads
    And I have only default brand

  Scenario: I create a download category
    And fake agent group exits
    When I send a POST request to "/api/v2/download_categories" with body:
"""
{
  "main":
  {
    "brand": ~defaultBrand~,
    "title": "Test download category",
    "usergroups": [~fake_group~]
  }
}
"""
    Then the response status code should be 201
    And the header "Location" should be equal to "/api/v2/download_categories/{lastCreatedId}"
    And the JSON node "data.title" should be equal to "Test download category"
    And the JSON node "data.slug" should contain "test-download-category"
    And the JSON node "data.brand" should be equal to "{defaultBrand}"
    And the JSON node "data.usergroups[0]" should be equal to "{fake_group}"
    And the JSON node "data.parent" should be null

  Scenario: I view the existing download category
    Given the following DownloadCategory records exist:
      | #   | title                     | Brand          |
      | dc1 | First Downloads Category  | {defaultBrand} |
    When I send a GET request to "/api/v2/download_categories/{dc1}"
    Then the response status code should be 200
    And the JSON node "data.title" should be equal to "First Downloads Category"
    And the JSON node "data.slug" should contain "first-downloads-category"
    And the JSON node "data.brand" should be equal to "{defaultBrand}"
    And the JSON node "data.parent" should be null

  Scenario: I create a children download category
    Given the following DownloadCategory records exist:
      | #   | title                     | Brand          |
      | dc1 | First Downloads Category  | {defaultBrand} |
    When I send a POST request to "/api/v2/download_categories" with body:
"""
{
  "main":
  {
    "brand": ~defaultBrand~,
    "title": "Test children category",
    "usergroups": [~fake_group~]
  },
  "parent": ~dc1~
}
"""
    Then the response status code should be 201
    And the header "Location" should be equal to "/api/v2/download_categories/{lastCreatedId}"
    And the JSON node "data.title" should be equal to "Test children category"
    And the JSON node "data.parent" should be equal to "{dc1}"
    And the JSON node "data.slug" should contain "test-children-category"

  Scenario: I edit title of the existing children download category
    Given the following DownloadCategory records exist:
      | #   | title                     | Brand          |
      | dc1 | First Downloads Category  | {defaultBrand} |
    When I send a PUT request to "/api/v2/download_categories/{dc1}" with body:
"""
{
  "main" : {
    "title": "Test Edited Children Download Category"
  }
}
"""
    Then the response status code should be 204
    When I send a GET request to "/api/v2/download_categories/{dc1}"
    And the response status code should be 200
    And the JSON node "data.title" should be equal to "Test Edited Children Download Category"
    And the JSON node "data.slug" should contain "first-downloads-category"

  Scenario: I delete created children download category
    Given the following DownloadCategory records exist:
      | #   | title                     | Brand          |
      | dc1 | First Downloads Category  | {defaultBrand} |
    And the following Download records exist:
      | #  | category |
      | d1 | {dc1}    |
    When I send a DELETE request to "/api/v2/download_categories/{dc1}"
    Then the response status code should be 200
    When I send a GET request to "{lastRequestUrl}"
    And the response status code should be 404
