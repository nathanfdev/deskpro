@new
Feature: /articles endpoint
  To CRUD DeskPRO downloads
  As an API user
  I want an API endpoint

  Background:
    Given agent and user exist

  Scenario: I create a download as agent
    Given I'm authenticated as agent
    And the following Language records exist:
      | #  | locale | sys_name |
      | l1 | so_ME  | some     |
      | l2 | fk     | fake     |
    And I have only default brand
    And the following DownloadCategory records exist:
      | #   | parent | title                     | slug                      | Brand          |
      | dc1 |        | First Downloads Category  | first_downloads_category  | {defaultBrand} |
      | dc2 | {dc1}  | Second Downloads Category | second_downloads_category | {defaultBrand} |
      | dc3 | {dc1}  | Third Downloads Category  | third_downloads_category  | {defaultBrand} |
    And I add "dc1" category usergroup relation "agent_all_safe_perms"
    And I add "dc2" category usergroup relation "agent_all_safe_perms"
    And I add "dc3" category usergroup relation "agent_all_safe_perms"
    When I send a POST request to "/api/v2/downloads" with body:
    """
{
  "main":
  {
    "title": "Test Download",
    "content": "<p>Some fake download description</p>",
    "person":  ~agent~,
    "language": ~l1~,
    "status": "hidden",
    "hidden_status": "draft",
    "content_input_type": "rte"
  },
  "category": ~dc2~
}
    """
    Then the response status code should be 201
    And the header "Location" should be equal to "/api/v2/downloads/{lastCreatedId}"
    And the JSON node "data.title" should be equal to "Test Download"
    And the JSON node "data.content" should be equal to "<p>Some fake download description</p>"
    And the JSON node "data.language" should be equal to "{l1}"
    And the JSON node "data.person" should be equal to "{agent}"
    And the JSON node "data.status" should be equal to "hidden"
    And the JSON node "data.hidden_status" should be equal to "draft"
    And the JSON node "data.category" should be equal to "{dc2}"

  Scenario: I view the created download as agent
    Given I'm authenticated as agent
    When I send a GET request to "/api/v2/downloads/{lastCreatedId}"
    Then the response status code should be 200
    And the JSON node "data.title" should be equal to "Test Download"
    And the JSON node "data.content" should be equal to "<p>Some fake download description</p>"
    And the JSON node "data.language" should be equal to "{l1}"
    And the JSON node "data.person" should be equal to "{agent}"
    And the JSON node "data.status" should be equal to "hidden"
    And the JSON node "data.hidden_status" should be equal to "draft"

  Scenario: I edit title of the created download and publish it as agent
    Given I'm authenticated as agent
    When I send a PUT request to "/api/v2/downloads/{lastCreatedId}" with body:
        """
{
  "main" : {
    "title": "Test Edited Download",
    "status": "published"
  }
}
    """
    Then the response status code should be 204
    When I send a GET request to "/api/v2/downloads/{lastCreatedId}"
    And the response status code should be 200
    And the JSON node "data.title" should be equal to "Test Edited Download"
    And the JSON node "data.content" should be equal to "<p>Some fake download description</p>"
    And the JSON node "data.language" should be equal to "{l1}"
    And the JSON node "data.person" should be equal to "{agent}"
    And the JSON node "data.status" should be equal to "published"
    And the JSON node "data.hidden_status" should be null

  Scenario: I change download category
    Given I'm authenticated as agent
    When I send a PUT request to "/api/v2/downloads/{lastCreatedId}" with body:
    """
{
  "category": ~dc3~
}
    """
    Then the response status code should be 204
    And I send a GET request to "/api/v2/downloads/{lastCreatedId}"
    And the response status code should be 200
    And the JSON node "data.category" should be equal to "{dc3}"

  Scenario: I try to create a download as user
    Given I'm authenticated as user
    When I send a POST request to "/api/v2/articles" with body:
    """
{}
    """
    And the response status code should be 403

  Scenario: I try to delete created download as agent without delete permissions
    Given I'm authenticated as agent
    And I remove "agent" usergroup relation "agent_all_safe_perms"
    And I remove "agent" usergroup relation "agent_all_perms"
    When I send a DELETE request to "/api/v2/downloads/{lastCreatedId}"
    Then the response status code should be 403

  Scenario: I delete created download as agent with all permissions
    Given I'm authenticated as agent
    And I add "agent" usergroup relation "agent_all_perms"
    When I send a DELETE request to "/api/v2/downloads/{lastCreatedId}"
    Then the response status code should be 200
    And I send a GET request to "/api/v2/downloads/{lastCreatedId}"
    And the response status code should be 404
