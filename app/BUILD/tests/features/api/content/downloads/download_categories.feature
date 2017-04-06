@new
Feature: /download_categories endpoint
  To CRUD DeskPRO download categories
  As an API user
  I want an API endpoint

  Background:
    Given agent and user exist
    And the following DownloadCategory records exist:
      | #   | parent | title            | slug             | depth | root |
      | dc1 |        | Parent Category  | parent_category  | 0     | ac1  |

  Scenario: I create a download category as agent
    Given I'm authenticated as agent
    And I have only default brand
    And fake agent group exits
    And I add agent usergroup relation agent_all_safe_perms
    When I send a POST request to "/api/v2/download_categories" with body:
    """
{
  "main":
  {
    "brand": ~defaultBrand~,
    "title": "Test download category",
    "usergroups": [~fake_group~]
  }
}
    """
    Then the response status code should be 201
    And the header "Location" should be equal to "/api/v2/download_categories/{lastCreatedId}"

  Scenario: I view the created download category as agent
    Given I'm authenticated as agent
    When I send a GET request to "/api/v2/download_categories/{lastCreatedId}"
    Then the response status code should be 200
    And the JSON node "data.title" should be equal to "Test download category"
    And the JSON node "data.slug" should contain "test-download-category"
    And the JSON node "data.brand" should be equal to "{defaultBrand}"
    And the JSON node "data.parent" should be null

  Scenario: I create a children download category as agent
    Given I'm authenticated as agent
    When I send a POST request to "/api/v2/download_categories" with body:
    """
{
  "main":
  {
    "brand": ~defaultBrand~,
    "title": "Test children category",
    "usergroups": [~fake_group~]
  },
  "parent": ~dc1~
}
    """
    Then the response status code should be 201
    And the header "Location" should be equal to "/api/v2/download_categories/{lastCreatedId}"
    And the JSON node "data.title" should be equal to "Test children category"
    And the JSON node "data.parent" should be equal to "{dc1}"
    And the JSON node "data.slug" should contain "test-children-category"

  Scenario: I edit title of the created children download category as agent
    Given I'm authenticated as agent
    When I send a PUT request to "/api/v2/download_categories/{lastCreatedId}" with body:
        """
{
  "main" : {
    "title": "Test Edited Children Download Category"
  }
}
    """
    Then the response status code should be 204
    When I send a GET request to "/api/v2/download_categories/{lastCreatedId}"
    And the response status code should be 200
    And the JSON node "data.title" should be equal to "Test Edited Children Download Category"
    And the JSON node "data.slug" should contain "test-children-category"

  Scenario: I delete created children download category as agent
    Given I'm authenticated as agent
    When I send a DELETE request to "/api/v2/download_categories/{lastCreatedId}"
    Then the response status code should be 200
    When I send a GET request to "/api/v2/download_categories/{lastCreatedId}"
    And the response status code should be 404
