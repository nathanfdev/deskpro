@new
Feature: /articles endpoint
  To CRUD DeskPRO articles by agent
  As an API user
  I want an API endpoint

  Background:
    Given agent and user exist
    Given I'm authenticated as agent
    And I have permissions to use Articles

  Scenario: I create an article
    Given the following ArticleCategory records exist:
      | #   | parent | is_agent | is_book | template_suffix | title          | slug           | display_order | depth | root |
      | ac1 |        | 1        | 0       | 0               | First Category | first_category | 0             | 0     | ac1  |
    And the following Language records exist:
      | #  | locale | sys_name |
      | l1 | so_ME  | some     |
    When I send a POST request to "/api/v2/articles" with body:
"""
{
  "main":
  {
    "title": "Test Article",
    "content": "<p>Some fake article content</p>",
    "person":  ~agent~,
    "language": ~l1~,
    "status": "hidden",
    "hidden_status": "draft",
    "content_input_type": "rte"
  },
  "categories": [~ac1~]
}
"""
    Then the response status code should be 201
    And the header "Location" should be equal to "/api/v2/articles/{lastCreatedId}"
    And the JSON node "data.title" should be equal to "Test Article"
    And the JSON node "data.content" should be equal to "<p>Some fake article content</p>"
    And the JSON node "data.language" should be equal to "{l1}"
    And the JSON node "data.person" should be equal to "{agent}"
    And the JSON node "data.slug" should contain "test-article"
    And the JSON node "data.status" should be equal to "hidden"
    And the JSON node "data.hidden_status" should be equal to "draft"
    And the JSON node "data.categories[0]" should be equal to "{~ac1~}"

  Scenario: I view an article
    Given the following Article records exist:
      | #   | title                 | content             |
      | ar1 | Some article for view | <p>Some content</p> |
    When I send a GET request to "/api/v2/articles/{ar1}"
    Then the response status code should be 200
    And the JSON node "data.title" should be equal to "Some article for view"
    And the JSON node "data.slug" should contain "some-article-for-view"
    And the JSON node "data.content" should be equal to "<p>Some content</p>"

  Scenario: I edit title of the existing article and publish it
    Given the following Article records exist:
      | #   | title                 | content             | status | hidden_status |
      | ar1 | Some article for view | <p>Some content</p> | hidden | draft         |
    When I send a PUT request to "/api/v2/articles/{ar1}" with body:
"""
{
  "main" : {
    "title": "Test Edited Article",
    "status": "published"
  }
}
"""
    Then the response status code should be 204
    When I send a GET request to "/api/v2/articles/{ar1}"
    And the response status code should be 200
    And the JSON node "data.title" should be equal to "Test Edited Article"
    And the JSON node "data.slug" should contain "test-edited-article"
    And the JSON node "data.content" should be equal to "<p>Some content</p>"
    And the JSON node "data.status" should be equal to "published"
    And the JSON node "data.hidden_status" should be null

  Scenario: I delete existing article
    Given I have only default brand
    And the following ArticleCategory records exist:
      | #   | is_agent | template_suffix | title          | slug           | root | brand          |
      | ac1 | 0        | 0               | First Category | first_category | ac1  | {defaultBrand} |
    And the following Article records exist:
      | #   | title                   | content             | status | hidden_status |
      | ar1 | Some article for delete | <p>Some content</p> | hidden | draft         |
    And I add  article ar1 to category ac1
    And I add agent usergroup relation "agent_all_perms"
    And I add "ac1" category usergroup relation "registered"
    When I send a DELETE request to "/api/v2/articles/{ar1}"
    Then the response status code should be 200
    And I send a GET request to "{lastRequestUrl}"
    And the response status code should be 404
