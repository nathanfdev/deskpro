@new
Feature: /news_categories endpoint
  To CRUD DeskPRO news categories
  As an API user
  I want an API endpoint

  Background:
    Given agent and user exist
    And the following NewsCategory records exist:
      | #   | parent | title            | slug             | depth | root |
      | nc1 |        | Parent Category  | parent_category  | 0     | ac1  |

  Scenario: I create a news category as agent
    Given I'm authenticated as agent
    And I have only default brand
    And fake agent group exits
    And I add agent usergroup relation agent_all_safe_perms
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

  Scenario: I view the created news category as agent
    Given I'm authenticated as agent
    When I send a GET request to "/api/v2/news_categories/{lastCreatedId}"
    Then the response status code should be 200
    And the JSON node "data.title" should be equal to "Test news category"
    And the JSON node "data.slug" should contain "test-news-category"
    And the JSON node "data.brand" should be equal to "{defaultBrand}"
    And the JSON node "data.parent" should be null

  Scenario: I create a children news category as agent
    Given I'm authenticated as agent
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

  Scenario: I edit title of the created children news category as agent
    Given I'm authenticated as agent
    When I send a PUT request to "/api/v2/news_categories/{lastCreatedId}" with body:
        """
{
  "main" : {
    "title": "Test Edited Children News Category"
  }
}
    """
    Then the response status code should be 204
    When I send a GET request to "/api/v2/news_categories/{lastCreatedId}"
    And the response status code should be 200
    And the JSON node "data.title" should be equal to "Test Edited Children News Category"
    And the JSON node "data.slug" should contain "test-children-category"

  Scenario: I delete created children news category as agent
    Given I'm authenticated as agent
    When I send a DELETE request to "/api/v2/news_categories/{lastCreatedId}"
    Then the response status code should be 200
    When I send a GET request to "/api/v2/news_categories/{lastCreatedId}"
    And the response status code should be 404
