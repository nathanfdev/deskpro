@new
Feature: /news_categories endpoint
  To CRUD DeskPRO news categories by agent
  As an API user
  I want an API endpoint

  Background:
    Given agent and user exist
    And I'm authenticated as agent
    And I have permissions to use News
    And I have only default brand

  Scenario: I create a news category
    Given fake agent group exits
    When I send a POST request to "/api/v2/news_categories" with body:
"""
{
  "main":
  {
    "brand": ~defaultBrand~,
    "title": "Test news category",
    "usergroups": [~fake_group~]
  }
}
"""
    Then the response status code should be 201
    And the header "Location" should be equal to "/api/v2/news_categories/{lastCreatedId}"
    And the JSON node "data.title" should be equal to "Test news category"
    And the JSON node "data.slug" should contain "test-news-category"
    And the JSON node "data.brand" should be equal to "{defaultBrand}"
    And the JSON node "data.usergroups[0]" should be equal to "{fake_group}"

  Scenario: I view the existing news category
    Given the following NewsCategory records exist:
      | #   | parent | title              | slug               | brand          |
      | nc1 |        | Test news category | test-news-category | {defaultBrand} |
    When I send a GET request to "/api/v2/news_categories/{nc1}"
    Then the response status code should be 200
    And the JSON node "data.title" should be equal to "Test news category"
    And the JSON node "data.slug" should contain "test-news-category"
    And the JSON node "data.brand" should be equal to "{defaultBrand}"
    And the JSON node "data.parent" should be null

  Scenario: I view the full list of existing news categories
    Given I have only default brand
    And only the following NewsCategory records exist:
      | #   | parent | title            | root | brand          |
      | nc1 |        | Test-1 category  | nc1  | {defaultBrand} |
      | nc2 | {nc1}  | Test-2 category  | nc1  | {defaultBrand} |
      | nc3 |        | Test-3 category  | nc3  | {defaultBrand} |
      | nc4 | {nc3}  | Test-4 category  | nc3  | {defaultBrand} |
      | nc5 |        | Test-5 category  | nc4  | {defaultBrand} |
    When I send a GET request to "/api/v2/news_categories"
    Then the response status code should be 200
    And the JSON node "data" should have 5 elements
    And the JSON node "meta.pagination.total_pages" should be equal to 1

  Scenario: I view the full list of existing news categories filtered by brand
    Given I have only default brand
    And only the following NewsCategory records exist:
      | #   | parent | title            | root | brand          |
      | nc1 |        | Test-1 category  | nc1  | {defaultBrand} |
      | nc2 | {nc1}  | Test-2 category  | nc1  | {defaultBrand} |
      | nc3 |        | Test-3 category  | nc3  | {defaultBrand} |
      | nc4 | {nc3}  | Test-4 category  | nc3  | {defaultBrand} |
      | nc5 |        | Test-5 category  | nc4  |                |
    When I send a GET request to "/api/v2/news_categories?brands={defaultBrand}"
    Then the response status code should be 200
    And the JSON node "data" should have 4 elements
    And the JSON node "meta.pagination.total_pages" should be equal to 1

  Scenario: I view the list of children news categories filtered by parent
    Given I have only default brand
    And only the following NewsCategory records exist:
      | #   | parent | title            | root | brand          |
      | nc1 |        | Test-1 category  | nc1  | {defaultBrand} |
      | nc2 | {nc1}  | Test-2 category  | nc1  | {defaultBrand} |
      | nc3 |        | Test-3 category  | nc3  | {defaultBrand} |
      | nc4 | {nc3}  | Test-4 category  | nc3  | {defaultBrand} |
      | nc5 |        | Test-5 category  | nc4  |                |
    When I send a GET request to "/api/v2/news_categories?parent={nc1}"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "meta.pagination.total_pages" should be equal to 1

  Scenario: I view the list of existing news categories paginated by 3 per page
    Given I have only default brand
    And only the following NewsCategory records exist:
      | #   | parent | title            | root | brand          |
      | nc1 |        | Test-1 category  | nc1  | {defaultBrand} |
      | nc2 | {nc1}  | Test-2 category  | nc1  | {defaultBrand} |
      | nc3 |        | Test-3 category  | nc3  | {defaultBrand} |
      | nc4 | {nc3}  | Test-4 category  | nc3  | {defaultBrand} |
      | nc5 |        | Test-5 category  | nc4  | {defaultBrand} |
    When I send a GET request to "/api/v2/news_categories?count=3"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "meta.pagination.total_pages" should be equal to 2

  Scenario: I create a children news category
    Given the following NewsCategory records exist:
      | #   | parent | title              | slug               | brand          |
      | nc1 |        | Test news category | test-news-category | {defaultBrand} |
    When I send a POST request to "/api/v2/news_categories" with body:
"""
{
  "main":
  {
    "brand": ~defaultBrand~,
    "title": "Test children category",
    "usergroups": [~fake_group~]
  },
  "parent": ~nc1~
}
"""
    Then the response status code should be 201
    And the header "Location" should be equal to "/api/v2/news_categories/{lastCreatedId}"
    And the JSON node "data.title" should be equal to "Test children category"
    And the JSON node "data.parent" should be equal to "{nc1}"
    And the JSON node "data.slug" should contain "test-children-category"

  Scenario: I edit title of the existing news category
    Given the following NewsCategory records exist:
      | #   | title              | brand          |
      | nc1 | Test news category | {defaultBrand} |
    When I send a PUT request to "/api/v2/news_categories/{nc1}" with body:
"""
{
  "main" : {
    "title": "Test Edited News Category"
  }
}
"""
    Then the response status code should be 204
    When I send a GET request to "/api/v2/news_categories/{nc1}"
    And the response status code should be 200
    And the JSON node "data.title" should be equal to "Test Edited News Category"
    And the JSON node "data.slug" should contain "test-news-category"

  Scenario: I delete created children news category as agent
    Given the following NewsCategory records exist:
      | #   | title              | brand          |
      | nc1 | Test news category | {defaultBrand} |
    And the following News records exist:
      | #  | category |
      | n1 | {nc1}    |
    When I send a DELETE request to "/api/v2/news_categories/{nc1}"
    Then the response status code should be 200
    When I send a GET request to "{lastRequestUrl}"
    And the response status code should be 404
