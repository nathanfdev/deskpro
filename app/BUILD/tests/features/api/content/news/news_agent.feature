@new
Feature: /news endpoint
  To CRUD DeskPRO news by agent
  As an API user
  I want an API endpoint

  Background:
    Given agent and user exist
    And I'm authenticated as agent
    And I have permissions to use News

  Scenario: I create a news
    And the following Language records exist:
      | #  |
      | l1 |
    And I have only default brand
    And the following NewsCategory records exist:
      | #   | Parent | Title               | Slug                | Brand          |
      | nc1 |        | First News Category | first_news_category | {defaultBrand} |
    And I add "nc1" category usergroup relation "registered"
    When I send a POST request to "/api/v2/news" with body:
"""
{
  "main" : {
    "title": "Test News",
    "content": "<p>Some fake news content</p>",
    "person":  ~agent~,
    "language": ~l1~,
    "status": "hidden",
    "hidden_status": "draft",
    "content_input_type": "rte"
  },
  "category": ~nc1~
}
"""
    Then the response status code should be 201
    And print last JSON response
    And the header "Location" should be equal to "/api/v2/news/{lastCreatedId}"
    And the JSON node "data.title" should be equal to "Test News"
    And the JSON node "data.content" should be equal to "<p>Some fake news content</p>"
    And the JSON node "data.language" should be equal to "{l1}"
    And the JSON node "data.person" should be equal to "{agent}"
    And the JSON node "data.status" should be equal to "hidden"
    And the JSON node "data.hidden_status" should be equal to "draft"
    And the JSON node "data.category" should be equal to "{nc1}"

  Scenario: I view the existing news
    Given the following Language records exist:
      | #  | locale | sys_name |
      | l1 | so_ME  | some     |
    And the following News records exist:
      | #  | title     | content             | language |
      | n1 | Test News | <p>News content</p> | {l1}     |
    When I send a GET request to "/api/v2/news/{n1}"
    Then the response status code should be 200
    And the JSON node "data.title" should be equal to "Test News"
    And the JSON node "data.slug" should contain "test-news"
    And the JSON node "data.content" should be equal to "<p>News content</p>"
    And the JSON node "data.language" should be equal to "{l1}"

  Scenario: I edit title of the existing news and publish it
    Given the following News records exist:
      | #  | title     | content             | status | hidden_status |
      | n1 | Test News | <p>News content</p> | hidden | draft         |
    When I send a PUT request to "/api/v2/news/{n1}" with body:
"""
{
  "main": {
    "title": "Test Edited News",
    "status": "published"
  }
}
"""
    Then the response status code should be 204
    When I send a GET request to "/api/v2/news/{n1}"
    And the response status code should be 200
    And the JSON node "data.title" should be equal to "Test Edited News"
    And the JSON node "data.slug" should contain "test-edited-news"
    And the JSON node "data.content" should be equal to "<p>News content</p>"
    And the JSON node "data.status" should be equal to "published"
    And the JSON node "data.hidden_status" should be null

  Scenario: I change news category
    Given I have only default brand
    And the following NewsCategory records exist:
      | #   | Parent | Title                | Brand          |
      | nc1 |        | First News Category  | {defaultBrand} |
      | nc2 |        | Second News Category | {defaultBrand} |
    And I add "nc1" category usergroup relation "registered"
    And I add "nc2" category usergroup relation "registered"
    And the following News records exist:
      | #  | category |
      | n1 | {nc1}    |
    When I send a PUT request to "/api/v2/news/{n1}" with body:
    """
{
  "category": ~nc2~
}
    """
    Then the response status code should be 204
    And I send a GET request to "/api/v2/news/{n1}"
    And the response status code should be 200
    And the JSON node "data.category" should be equal to "{nc2}"

  Scenario: I delete created news as agent with all permissions
    Given I have only default brand
    And the following NewsCategory records exist:
      | #   | Parent | Title                | Brand          |
      | nc1 |        | First News Category  | {defaultBrand} |
    And the following News records exist:
      | #  | category |
      | n1 | {nc1}    |
    And I add agent usergroup relation "agent_all_perms"
    And I add "nc1" category usergroup relation "registered"
    When I send a DELETE request to "/api/v2/news/{n1}"
    Then the response status code should be 200
    And I send a GET request to "{lastRequestUrl}"
    And the response status code should be 404
