@new
Feature: /downloads endpoint
  To CRUD DeskPRO downloads by agent
  As an API user
  I want an API endpoint

  Background:
    Given agent and user exist
    And I'm authenticated as agent
    And I have permissions to use Downloads

  Scenario: I create a download
    Given the following Language records exist:
      | #  | locale | sys_name |
      | l1 | so_ME  | some     |
    And the following Usergroup records exist:
      | #   | sys_name   | Title      |
      | ug1 | registered | Registered |
    And I have only default brand
    And the following DownloadCategory records exist:
      | #   | parent | title                    | Brand          | Usergroups |
      | dc1 |        | First Downloads Category | {defaultBrand} | [{ug1}]    |
    And I create blob with auth code "AAA"
    When I send a POST request to "/api/v2/downloads" with body:
    """
{
  "title": "Test Download",
  "content": "<p>Some fake download description</p>",
  "person":  ~agent~,
  "language": ~l1~,
  "status": "hidden.draft",
  "content_input_type": "rte",
  "category": ~dc1~,
  "blob": "AAA"
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
    And the JSON node "data.category" should be equal to "{dc1}"

  Scenario: I view the existing download as agent
    Given the following Language records exist:
      | #  | locale | sys_name |
      | l1 | so_ME  | some     |
    And the following Download records exist:
      | #  | title         | content                     | language |
      | d1 | Test Download | <p>Download description</p> | {l1}     |
    When I send a GET request to "/api/v2/downloads/{d1}"
    Then the response status code should be 200
    And the JSON node "data.title" should be equal to "Test Download"
    And the JSON node "data.content" should be equal to "<p>Download description</p>"
    And the JSON node "data.language" should be equal to "{l1}"

  Scenario: I edit title of the existing download and publish it
    Given the following Download records exist:
      | #  | title         | status | hidden_status |
      | d1 | Test Download | hidden | draft         |
    When I send a PUT request to "/api/v2/downloads/{d1}" with body:
    """
{
  "title": "Test Edited Download",
  "status": "published"
}
    """
    Then the response status code should be 204
    When I send a GET request to "/api/v2/downloads/{d1}"
    Then the response status code should be 200
    And the JSON node "data.title" should be equal to "Test Edited Download"
    And the JSON node "data.status" should be equal to "published"
    And the JSON node "data.hidden_status" should be null

  Scenario: I change download category
    Given the following Usergroup records exist:
      | #   | sys_name   | Title      |
      | ug1 | registered | Registered |
    And I have only default brand
    And the following DownloadCategory records exist:
      | #   | title                     | Brand          | Usergroups |
      | dc1 | First Downloads Category  | {defaultBrand} | [{ug1}]    |
      | dc2 | Second Downloads Category | {defaultBrand} | [{ug1}]    |
    And the following Download records exist:
      | #  | category |
      | d1 | {dc1}    |
    When I send a PUT request to "/api/v2/downloads/{d1}" with body:
    """
{
  "category": ~dc2~
}
    """
    Then the response status code should be 204
    And I send a GET request to "/api/v2/downloads/{d1}"
    And the response status code should be 200
    And the JSON node "data.category" should be equal to "{dc2}"

  Scenario: I delete existing download
    Given I have only default brand
    And the following Usergroup records exist:
      | #   | sys_name   | Title      |
      | ug1 | registered | Registered |
    And the following DownloadCategory records exist:
      | #   | parent | title                    | Brand          | Usergroups |
      | dc1 |        | First Downloads Category | {defaultBrand} | [{ug1}]    |
    And the following Download records exist:
      | #  | category |
      | d1 | {dc1}    |
    And I add agent usergroup relation "agent_all_perms"
    When I send a DELETE request to "/api/v2/downloads/{d1}"
    Then the response status code should be 200
    And I send a GET request to "{lastRequestUrl}"
    And the response status code should be 404
