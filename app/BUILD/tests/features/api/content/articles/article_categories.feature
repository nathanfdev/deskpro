@new
Feature: /article_categories endpoint
  To CRUD DeskPRO articles
  As an API user
  I want an API endpoint

  Background:
    Given agent and user exist

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
    And print last response
    Then the response status code should be 201
    And the header "Location" should be equal to "/api/v2/article_categories/{lastCreatedId}"
    And the JSON node "data.title" should be equal to "Test article category"
    And the JSON node "data.brand" should be equal to "{defaultBrand}"
