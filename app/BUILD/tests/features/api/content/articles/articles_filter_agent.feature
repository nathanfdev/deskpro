@new
Feature: /articles endpoint
  To filter DeskPRO articles by agent
  As an API user
  I want an API endpoint

  Background:
    Given agent and user exist
    Given I'm authenticated as agent
    And I have permissions to use Articles
    And I have only default brand
    And the following ArticleCategory records exist:
      | #   | parent | is_agent | title                  | slug                   | root | brand          |
      | ac1 |        | 1        | Test article category  | test-article-category  | ac1  | {defaultBrand} |
    And only the following Article records exist:
      | #   | title     | content             | status    | hidden_status | person  | to category |
      | ar1 | Article-1 | <p>Some content</p> | hidden    | unpublished   | {user}  | {ac1}       |
      | ar2 | Article-2 | <p>Some content</p> | hidden    | draft         | {user}  | {ac1}       |
      | ar3 | Article-3 | <p>Some content</p> | published |               | {user}  |             |
      | ar4 | Article-4 | <p>Some content</p> | archived  |               | {agent} |             |
      | ar5 | Article-5 | <p>Some content</p> | hidden    | spam          | {agent} |             |
      | ar5 | Article-6 | <p>Some content</p> | hidden    | deleted       | {user}  |             |

  Scenario: I view the full list of existing articles
    Given I send a GET request to "/api/v2/articles"
    Then the response status code should be 200
    And the JSON node "data" should have 6 elements
    And the JSON node "meta.pagination.total_pages" should be equal to 1

  Scenario: I view the full list of existing articles paginated by 3 per page
    Given I send a GET request to "/api/v2/articles?count=3"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "meta.pagination.total_pages" should be equal to 2

  Scenario: I view the full list of existing articles created by agent
    Given I send a GET request to "/api/v2/articles?author={agent}"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements

  Scenario: I view the full list of existing articles created by user
    Given I send a GET request to "/api/v2/articles?author={user}"
    Then the response status code should be 200
    And the JSON node "data" should have 4 elements

  Scenario: I view the full list of existing articles with "published" status
    Given I send a GET request to "/api/v2/articles?status=published"
    Then the response status code should be 200
    And the JSON node "data" should have 1 elements

  Scenario: I view the full list of existing articles with "archived" status
    Given I send a GET request to "/api/v2/articles?status=archived"
    Then the response status code should be 200
    And the JSON node "data" should have 1 elements

  Scenario: I view the full list of existing articles with "hidden" status
    Given I send a GET request to "/api/v2/articles?status=hidden"
    Then the response status code should be 200
    And the JSON node "data" should have 4 elements

  Scenario: I view the full list of existing articles with "draft" hidden status
    Given I send a GET request to "/api/v2/articles?hidden_status=draft"
    Then the response status code should be 200
    And the JSON node "data" should have 1 elements

  Scenario: I view the full list of existing articles with "unpublished" hidden status
    Given I send a GET request to "/api/v2/articles?hidden_status=unpublished"
    Then the response status code should be 200
    And the JSON node "data" should have 1 elements

  Scenario: I view the full list of existing articles with "spam" hidden status
    Given I send a GET request to "/api/v2/articles?hidden_status=spam"
    Then the response status code should be 200
    And the JSON node "data" should have 1 elements

  Scenario: I view the full list of existing articles with "deleted" hidden status
    Given I send a GET request to "/api/v2/articles?hidden_status=deleted"
    Then the response status code should be 200
    And the JSON node "data" should have 1 elements
