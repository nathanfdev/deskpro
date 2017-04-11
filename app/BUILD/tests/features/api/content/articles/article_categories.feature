@new
Feature: /article_categories endpoint
  To CRUD DeskPRO articles categories
  As an API user
  I want an API endpoint

  Background:
    Given agent and user exist
    And I'm authenticated as agent
    And I have permissions to use Articles

  Scenario: I create an article category as agent
    Given I have only default brand
    And fake agent group exits
    When I send a POST request to "/api/v2/article_categories" with body:
"""
{
  "brand": ~defaultBrand~,
  "title": "Test article category",
  "usergroups": [~fake_group~]
}
"""
    Then the response status code should be 201
    And the header "Location" should be equal to "/api/v2/article_categories/{lastCreatedId}"

  Scenario: I view the existing article category
    Given I have only default brand
    And the following ArticleCategory records exist:
      | #   | parent | is_agent | title                  | slug                   | root | brand          |
      | ac1 |        | 1        | Test article category  | test-article-category  | ac1  | {defaultBrand} |
    When I send a GET request to "/api/v2/article_categories/{ac1}"
    Then the response status code should be 200
    And the JSON node "data.title" should be equal to "Test article category"
    And the JSON node "data.slug" should contain "test-article-category"
    And the JSON node "data.brand" should be equal to "{defaultBrand}"
    And the JSON node "data.parent" should be null

  Scenario: I view the full list of existing article categories
    Given I have only default brand
    And only the following ArticleCategory records exist:
      | #   | parent | is_agent | title            | root | brand          |
      | ac1 |        | 1        | Test-1 category  | ac1  | {defaultBrand} |
      | ac2 | {ac1}  | 1        | Test-2 category  | ac1  | {defaultBrand} |
      | ac3 |        | 0        | Test-3 category  | ac3  | {defaultBrand} |
      | ac4 | {ac3}  | 0        | Test-4 category  | ac3  | {defaultBrand} |
      | ac5 |        | 1        | Test-5 category  | ac4  | {defaultBrand} |
    When I send a GET request to "/api/v2/article_categories"
    Then the response status code should be 200
    And the JSON node "data" should have 5 elements
    And the JSON node "meta.pagination.total_pages" should be equal to 1

  Scenario: I view the full list of existing article categories filtered by brand
    Given I have only default brand
    And only the following ArticleCategory records exist:
      | #   | parent | is_agent | title            | root | brand          |
      | ac1 |        | 1        | Test-1 category  | ac1  | {defaultBrand} |
      | ac2 | {ac1}  | 1        | Test-2 category  | ac1  | {defaultBrand} |
      | ac3 |        | 0        | Test-3 category  | ac3  | {defaultBrand} |
      | ac4 | {ac3}  | 0        | Test-4 category  | ac3  | {defaultBrand} |
      | ac5 |        | 1        | Test-5 category  | ac4  |                |
    When I send a GET request to "/api/v2/article_categories?brands={defaultBrand}"
    Then the response status code should be 200
    And the JSON node "data" should have 4 elements
    And the JSON node "meta.pagination.total_pages" should be equal to 1

  Scenario: I view the list of children article categories filtered by parent
    Given I have only default brand
    And only the following ArticleCategory records exist:
      | #   | parent | is_agent | title            | root | brand          |
      | ac1 |        | 1        | Test-1 category  | ac1  | {defaultBrand} |
      | ac2 | {ac1}  | 1        | Test-2 category  | ac1  | {defaultBrand} |
      | ac3 |        | 0        | Test-3 category  | ac3  | {defaultBrand} |
      | ac4 | {ac3}  | 0        | Test-4 category  | ac3  | {defaultBrand} |
      | ac5 |        | 1        | Test-5 category  | ac4  |                |
    When I send a GET request to "/api/v2/article_categories?parent={ac1}"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "meta.pagination.total_pages" should be equal to 1

  Scenario: I view the list of existing article categories paginated by 3 per page
    Given I have only default brand
    And only the following ArticleCategory records exist:
      | #   | parent | is_agent | title            | root | brand          |
      | ac1 |        | 1        | Test-1 category  | ac1  | {defaultBrand} |
      | ac2 | {ac1}  | 1        | Test-2 category  | ac1  | {defaultBrand} |
      | ac3 |        | 0        | Test-3 category  | ac3  | {defaultBrand} |
      | ac4 | {ac3}  | 0        | Test-4 category  | ac3  | {defaultBrand} |
      | ac5 |        | 1        | Test-5 category  | ac4  | {defaultBrand} |
    When I send a GET request to "/api/v2/article_categories?count=3"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "meta.pagination.total_pages" should be equal to 2

  Scenario: I create a children article category
    Given I have only default brand
    And fake agent group exits
    And the following ArticleCategory records exist:
      | #   | parent | is_agent | is_book | title           | slug            | depth | root |
      | ac1 |        | 1        | 0       | First Category  | first-category  | 0     | ac1  |
    When I send a POST request to "/api/v2/article_categories" with body:
"""
{
  "brand": ~defaultBrand~,
  "title": "Test children category",
  "usergroups": [~fake_group~],
  "parent": ~ac1~
}
"""
    Then the response status code should be 201
    And the header "Location" should be equal to "/api/v2/article_categories/{lastCreatedId}"
    And the JSON node "data.title" should be equal to "Test children category"
    And the JSON node "data.parent" should be equal to "{ac1}"
    And the JSON node "data.slug" should contain "test-children-category"

  Scenario: I edit title of the existing article category
    Given the following ArticleCategory records exist:
      | #   | parent | is_agent | is_book | title           | slug            | depth | root |
      | ac1 |        | 1        | 0       | First Category  | first-category  | 0     | ac1  |
    When I send a PUT request to "/api/v2/article_categories/{ac1}" with body:
"""
{
  "title": "Edited Article Category"
}
"""
    Then the response status code should be 204
    When I send a GET request to "/api/v2/article_categories/{ac1}"
    And the response status code should be 200
    And the JSON node "data.title" should be equal to "Edited Article Category"
    And the JSON node "data.slug" should contain "first-category"

  Scenario: I delete existing article category
    Given the following ArticleCategory records exist:
      | #   | parent | is_agent | is_book | title           | slug            | depth | root |
      | ac1 |        | 1        | 0       | First Category  | first-category  | 0     | ac1  |
    And the following Article records exist:
      | #   | title                 | content             | status | hidden_status |
      | ar1 | Some article for view | <p>Some content</p> | hidden | draft         |
    And I add  article ar1 to category ac1
    When I send a DELETE request to "/api/v2/article_categories/{ac1}"
    Then the response status code should be 200
    When I send a GET request to "{lastRequestUrl}"
    Then the response status code should be 404
    When I send a GET request to "/api/v2/articles/{ar1}"
    Then the response status code should be 200
