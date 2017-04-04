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
    And the following NewsCategory records exist:
      | #   | parent | title                | slug                 |
      | ac1 |        | First News Category  | first_news_category  |
      | ac2 | {ac1}  | Second News Category | second_news_category |
      | ac3 | {ac1}  | Third News Category  | third_news_category  |
      | ac4 |        | Fourth News Category | fourth_news_category |
    When I send a POST request to "/api/v2/news" with body:
    """
{
  "title": "Test News",
  "content": "<p>Some fake news content</p>",
  "person":  ~agent~,
  "language": ~l1~,
  "status": "hidden",
  "hidden_status": "draft",
  "content_input_type": "rte"
}
    """
    Then the response status code should be 201
    And the header "Location" should be equal to "/api/v2/news/{lastCreatedId}"
    And the JSON node "data.title" should be equal to "Test News"
    And the JSON node "data.content" should be equal to "<p>Some fake news content</p>"
    And the JSON node "data.language" should be equal to "{l1}"
    And the JSON node "data.person" should be equal to "{agent}"
    And the JSON node "data.status" should be equal to "hidden"
    And the JSON node "data.hidden_status" should be equal to "draft"

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
  "title": "Test Edited News",
  "status": "published"
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

  Scenario: I try to delete created news as agent without delete permissions
    Given I'm authenticated as agent
    When I send a DELETE request to "/api/v2/news/{lastCreatedId}"
    Then the response status code should be 403

  Scenario: I delete created news as agent with all permissions
    Given I'm authenticated as agent
    And agent is member of the all permissions group
    When I send a DELETE request to "/api/v2/news/{lastCreatedId}"
    Then the response status code should be 200
    When I send a GET request to "/api/v2/news/{lastCreatedId}"
    And the response status code should be 404
    And agent is removed from the all permissions group

  Scenario: I try to create an article as user
    Given I'm authenticated as user
    When I send a POST request to "/api/v2/news" with body:
    """
{}
    """
    And the response status code should be 403
