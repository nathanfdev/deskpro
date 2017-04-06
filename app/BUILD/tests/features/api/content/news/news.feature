@new
Feature: /news endpoint
  To CRUD DeskPRO news
  As an API user
  I want an API endpoint

  Background:
    Given agent and user exist

  Scenario: I create a news as agent
    Given I'm authenticated as agent
    And the following Language records exist:
      | #  | locale | sys_name |
      | l1 | so_ME  | some     |
      | l2 | fk     | fake     |
    And I have only default brand
    And the following NewsCategory records exist:
      | #   | Parent | Title                | Slug                 | Brand          |
      | nc1 |        | First News Category  | first_news_category  | {defaultBrand} |
      | nc2 | {nc1}  | Second News Category | second_news_category | {defaultBrand} |
      | nc3 | {nc1}  | Third News Category  | third_news_category  | {defaultBrand} |
      | nc4 |        | Fourth News Category | fourth_news_category | {defaultBrand} |
    And I add "nc1" category usergroup relation "agent_all_safe_perms"
    And I add "nc2" category usergroup relation "agent_all_safe_perms"
    And I add "nc3" category usergroup relation "agent_all_safe_perms"
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
  "category": ~nc2~
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
    And the JSON node "data.category" should be equal to "{nc2}"

  Scenario: I view the created news as agent
    Given I'm authenticated as agent
    When I send a GET request to "/api/v2/news/{lastCreatedId}"
    Then the response status code should be 200
    And the JSON node "data.title" should be equal to "Test News"
    And the JSON node "data.slug" should contain "test-news"
    And the JSON node "data.content" should be equal to "<p>Some fake news content</p>"
    And the JSON node "data.language" should be equal to "{l1}"
    And the JSON node "data.person" should be equal to "{agent}"
    And the JSON node "data.status" should be equal to "hidden"
    And the JSON node "data.hidden_status" should be equal to "draft"

  Scenario: I edit title of the created article and publish it as agent
    Given I'm authenticated as agent
    When I send a PUT request to "/api/v2/news/{lastCreatedId}" with body:
    """
{
  "main": {
    "title": "Test Edited News",
    "status": "published"
  }
}
    """
    Then the response status code should be 204
    When I send a GET request to "/api/v2/news/{lastCreatedId}"
    And the response status code should be 200
    And the JSON node "data.title" should be equal to "Test Edited News"
    And the JSON node "data.slug" should contain "test-edited-news"
    And the JSON node "data.content" should be equal to "<p>Some fake news content</p>"
    And the JSON node "data.language" should be equal to "{l1}"
    And the JSON node "data.person" should be equal to "{agent}"
    And the JSON node "data.status" should be equal to "published"
    And the JSON node "data.hidden_status" should be null

  Scenario: I change news category
    Given I'm authenticated as agent
    When I send a PUT request to "/api/v2/news/{lastCreatedId}" with body:
    """
{
  "category": ~nc3~
}
    """
    Then the response status code should be 204
    And I send a GET request to "/api/v2/news/{lastCreatedId}"
    And the response status code should be 200
    And the JSON node "data.category" should be equal to "{nc3}"

  Scenario: I try to create a news as user
    Given I'm authenticated as user
    When I send a POST request to "/api/v2/news" with body:
    """
{}
    """
    And the response status code should be 403

  Scenario: I try to delete created news as agent without delete permissions
    Given I'm authenticated as agent
    And I remove agent usergroup relation agent_all_safe_perms
    And I remove agent usergroup relation agent_all_perms
    When I send a DELETE request to "/api/v2/news/{lastCreatedId}"
    Then the response status code should be 403

  Scenario: I delete created news as agent with all permissions
    Given I'm authenticated as agent
    And I add agent usergroup relation agent_all_perms
    When I send a DELETE request to "/api/v2/news/{lastCreatedId}"
    Then the response status code should be 200
    And I send a GET request to "/api/v2/news/{lastCreatedId}"
    And the response status code should be 404
