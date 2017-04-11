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
  "brand": ~defaultBrand~,
  "title": "Test download category",
  "usergroups": [~fake_group~]
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

  Scenario: I view the full list of existing download categories
    Given I have only default brand
    And only the following DownloadCategory records exist:
      | #   | parent | title            | root | brand          |
      | dc1 |        | Test-1 category  | dc1  | {defaultBrand} |
      | dc2 | {dc1}  | Test-2 category  | dc1  | {defaultBrand} |
      | dc3 |        | Test-3 category  | dc3  | {defaultBrand} |
      | dc4 | {dc3}  | Test-4 category  | dc3  | {defaultBrand} |
      | dc5 |        | Test-5 category  | dc4  | {defaultBrand} |
    When I send a GET request to "/api/v2/download_categories"
    Then the response status code should be 200
    And the JSON node "data" should have 5 elements
    And the JSON node "meta.pagination.total_pages" should be equal to 1

  Scenario: I view the full list of existing download categories filtered by brand
    Given I have only default brand
    And only the following DownloadCategory records exist:
      | #   | parent | title            | root | brand          |
      | dc1 |        | Test-1 category  | dc1  | {defaultBrand} |
      | dc2 | {dc1}  | Test-2 category  | dc1  | {defaultBrand} |
      | dc3 |        | Test-3 category  | dc3  | {defaultBrand} |
      | dc4 | {dc3}  | Test-4 category  | dc3  | {defaultBrand} |
      | dc5 |        | Test-5 category  | dc4  |                |
    When I send a GET request to "/api/v2/download_categories?brands={defaultBrand}"
    Then the response status code should be 200
    And the JSON node "data" should have 4 elements
    And the JSON node "meta.pagination.total_pages" should be equal to 1

  Scenario: I view the list of children download categories filtered by parent
    Given I have only default brand
    And only the following DownloadCategory records exist:
      | #   | parent | title            | root | brand          |
      | dc1 |        | Test-1 category  | dc1  | {defaultBrand} |
      | dc2 | {dc1}  | Test-2 category  | dc1  | {defaultBrand} |
      | dc3 |        | Test-3 category  | dc3  | {defaultBrand} |
      | dc4 | {dc3}  | Test-4 category  | dc3  | {defaultBrand} |
      | dc5 |        | Test-5 category  | dc4  |                |
    When I send a GET request to "/api/v2/download_categories?parent={dc1}"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "meta.pagination.total_pages" should be equal to 1

  Scenario: I view the list of existing download categories paginated by 3 per page
    Given I have only default brand
    And only the following DownloadCategory records exist:
      | #   | parent | title            | root | brand          |
      | dc1 |        | Test-1 category  | dc1  | {defaultBrand} |
      | dc2 | {dc1}  | Test-2 category  | dc1  | {defaultBrand} |
      | dc3 |        | Test-3 category  | dc3  | {defaultBrand} |
      | dc4 | {dc3}  | Test-4 category  | dc3  | {defaultBrand} |
      | dc5 |        | Test-5 category  | dc4  | {defaultBrand} |
    When I send a GET request to "/api/v2/download_categories?count=3"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "meta.pagination.total_pages" should be equal to 2

  Scenario: I create a children download category
    Given the following DownloadCategory records exist:
      | #   | title                     | Brand          |
      | dc1 | First Downloads Category  | {defaultBrand} |
    When I send a POST request to "/api/v2/download_categories" with body:
"""
{
  "brand": ~defaultBrand~,
  "title": "Test children category",
  "usergroups": [~fake_group~],
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
  "title": "Test Edited Children Download Category"
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
    When I send a GET request to "/api/v2/downloads/{d1}"
    And the response status code should be 200
