@new
Feature: /articles endpoint
  To CRUD DeskPRO articles
  As an API user
  I want an API endpoint

  Background:
    Given agent and user exist
    And the following ArticleCategory records exist:
      | #   | parent | is_agent | is_book | template_suffix | title           | slug            | display_order | depth | root |
      | ac1 |        | 1        | 0       | 0               | First Category  | first_category  | 0             | 0     | ac1  |
      | ac2 | {ac1}  | 0        | 1       | 0               | Second Category | second_category | 1             | 1     | ac1  |
      | ac3 | {ac1}  | 1        | 1       | 0               | Third Category  | third_category  | 2             | 1     | ac1  |
      | ac4 |        | 0        | 0       | 0               | Fourth Category | fourth_category | 0             | 0     | ac2  |


  Scenario: I create an article as agent
    Given I'm authenticated as agent
    And the following Language records exist:
      | #  | locale | sys_name |
      | l1 | so_ME  | some     |
      | l2 | fk     | fake     |
    When I send a POST request to "/api/v2/articles" with body:
    """
{
  "title": "Test Article",
  "content": "<p>Some fake article content</p>",
  "person":  ~agent~,
  "language": ~l1~,
  "status": "hidden",
  "hidden_status": "draft",
  "content_input_type": "rte"
}
    """
    Then the response status code should be 201
    And the header "Location" should be equal to "/api/v2/articles/{lastCreatedId}"
    And the JSON node "data.title" should be equal to "Test Article"
    And the JSON node "data.content" should be equal to "<p>Some fake article content</p>"
    And the JSON node "data.language" should be equal to "{l1}"
    And the JSON node "data.person" should be equal to "{agent}"
    And the JSON node "data.status" should be equal to "hidden"
    And the JSON node "data.hidden_status" should be equal to "draft"

  Scenario: I view the created article as agent
    Given I'm authenticated as agent
    When I send a GET request to "/api/v2/articles/{lastCreatedId}"
    Then the response status code should be 200
    And the JSON node "data.title" should be equal to "Test Article"
    And the JSON node "data.slug" should contain "test-article"
    And the JSON node "data.content" should be equal to "<p>Some fake article content</p>"
    And the JSON node "data.language" should be equal to "{l1}"
    And the JSON node "data.person" should be equal to "{agent}"
    And the JSON node "data.status" should be equal to "hidden"
    And the JSON node "data.hidden_status" should be equal to "draft"

  Scenario: I edit title of the created article and publish it as agent
    Given I'm authenticated as agent
    When I send a PUT request to "/api/v2/articles/{lastCreatedId}" with body:
        """
{
  "title": "Test Edited Article",
  "status": "published"
}
    """
    Then the response status code should be 204
    When I send a GET request to "/api/v2/articles/{lastCreatedId}"
    And the response status code should be 200
    And the JSON node "data.title" should be equal to "Test Edited Article"
    And the JSON node "data.slug" should contain "test-edited-article"
    And the JSON node "data.content" should be equal to "<p>Some fake article content</p>"
    And the JSON node "data.language" should be equal to "{l1}"
    And the JSON node "data.person" should be equal to "{agent}"
    And the JSON node "data.status" should be equal to "published"
    And the JSON node "data.hidden_status" should be null

  Scenario: I try to delete created article as agent without delete permissions
    Given I'm authenticated as agent
    When I send a DELETE request to "/api/v2/articles/{lastCreatedId}"
    Then the response status code should be 403

  Scenario: I delete created article as agent with all permissions
    Given I'm authenticated as agent
    And agent is member of the all permissions group
    When I send a DELETE request to "/api/v2/articles/{lastCreatedId}"
    Then the response status code should be 200
    When I send a GET request to "/api/v2/articles/{lastCreatedId}"
    And the response status code should be 404
    And agent is removed from the all permissions group

  Scenario: I try to create an article as user
    Given I'm authenticated as user
    When I send a POST request to "/api/v2/articles" with body:
    """
{}
    """
    And the response status code should be 403
