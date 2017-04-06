@new
Feature: /article_categories endpoint
  To CRUD DeskPRO articles categories
  As an API user
  I want an API endpoint

  Background:
    Given agent and user exist
    And the following ArticleCategory records exist:
      | #   | parent | is_agent | is_book | title           | slug            | depth | root |
      | ac1 |        | 1        | 0       | First Category  | first_category  | 0     | ac1  |

  Scenario: I create an article category as agent
    Given I'm authenticated as agent
    And I have only default brand
    And fake agent group exits
    And I add agent usergroup relation agent_all_safe_perms
    When I send a POST request to "/api/v2/article_categories" with body:
    """
{
  "main":
  {
    "brand": ~defaultBrand~,
    "title": "Test article category",
    "usergroups": [~fake_group~]
  }
}
    """
    Then the response status code should be 201
    And the header "Location" should be equal to "/api/v2/article_categories/{lastCreatedId}"

  Scenario: I view the created article category as agent
    Given I'm authenticated as agent
    When I send a GET request to "/api/v2/article_categories/{lastCreatedId}"
    Then the response status code should be 200
    And the JSON node "data.title" should be equal to "Test article category"
    And the JSON node "data.slug" should contain "test-article-category"
    And the JSON node "data.brand" should be equal to "{defaultBrand}"
    And the JSON node "data.parent" should be null

  Scenario: I create a children article category as agent
    Given I'm authenticated as agent
    When I send a POST request to "/api/v2/article_categories" with body:
    """
{
  "main":
  {
    "brand": ~defaultBrand~,
    "title": "Test children category",
    "usergroups": [~fake_group~]
  },
  "parent": ~ac1~
}
    """
    Then the response status code should be 201
    And the header "Location" should be equal to "/api/v2/article_categories/{lastCreatedId}"
    And the JSON node "data.title" should be equal to "Test children category"
    And the JSON node "data.parent" should be equal to "{ac1}"
    And the JSON node "data.slug" should contain "test-children-category"

  Scenario: I edit title of the created children article category as agent
    Given I'm authenticated as agent
    When I send a PUT request to "/api/v2/article_categories/{lastCreatedId}" with body:
        """
{
  "main" : {
    "title": "Test Edited Children Article Category"
  }
}
    """
    Then the response status code should be 204
    When I send a GET request to "/api/v2/article_categories/{lastCreatedId}"
    And the response status code should be 200
    And the JSON node "data.title" should be equal to "Test Edited Children Article Category"
    And the JSON node "data.slug" should contain "test-children-category"

  Scenario: I delete created children article category as agent
    Given I'm authenticated as agent
    When I send a DELETE request to "/api/v2/article_categories/{lastCreatedId}"
    Then the response status code should be 200
    When I send a GET request to "/api/v2/article_categories/{lastCreatedId}"
    And the response status code should be 404
