@new
Feature: Content Templates
  To create/update/get Content Template
  As an API user
  I want an API endpoint

  Scenario: I select my content templates
    Given I'm authenticated as agent
    And only the following ContentTemplate records exist:
      | #   | Title            | Type     | Person  | Template         |
      | ct1 | Test template #1 | article  | {agent} | {"test": "test"} |
      | ct2 | Test template #2 | news     | {agent} | {"test": "test"} |
      | ct3 | Test template #3 | download | {agent} | {"test": "test"} |

    When I send a GET request to "/api/v2/content_templates"
    And the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].type" should be equal to "article"
    And the JSON node "data[1].type" should be equal to "news"
    And the JSON node "data[2].type" should be equal to "download"
    And the JSON node "data[0].template.test" should be equal to "test"

  Scenario: I select my content template
    Given I'm authenticated as agent
    And only the following ContentTemplate records exist:
      | #   | Title            | Type    | Person  | Template         |
      | ct1 | Test template #1 | article | {agent} | {"test": "test"} |

    When I send a GET request to "/api/v2/content_templates/{ct1}"
    And the response status code should be 200
    And the JSON node "data.type" should be equal to "article"
    And the JSON node "data.template.test" should be equal to "test"

  Scenario: I create a new content template
    Given I'm authenticated as agent
    And there are no "ContentTemplate" records
    When I send a POST request to "/api/v2/content_templates" with body:
    """
{
  "title": "Test content template",
  "type": "article",
  "template": {
    "name": "test article template"
  }
}
    """
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/content_templates/{lastCreatedId}"
    And the JSON node "data.title" should be equal to "Test content template"

    When I send a GET request to "/api/v2/content_templates/{lastCreatedId}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.title" should be equal to "Test content template"
    And the JSON node "data.type" should be equal to "article"
    And the JSON node "data.template.name" should exist
    And the JSON node "data.template.name" should be equal to "test article template"

  Scenario: I update the content template
    Given I'm authenticated as agent
    And only the following ContentTemplate records exist:
      | #   | Title            | Type    | Person  | Template         |
      | ct1 | Test template #1 | article | {agent} | {"test": "test"} |
    When I send a PUT request to "/api/v2/content_templates/{ct1}" with body:
    """
{
  "title": "Test content template",
  "type": "article",
  "template": {
    "name": "test article template"
  }
}
    """
    And the response status code should be 204

    When I send a GET request to "/api/v2/content_templates/{ct1}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.title" should be equal to "Test content template"
    And the JSON node "data.template.name" should exist
    And the JSON node "data.template.name" should be equal to "test article template"

  Scenario: I delete the content template
    Given I'm authenticated as agent
    And only the following ContentTemplate records exist:
      | #   | Title            | Type    | Person  | Template         |
      | ct1 | Test template #1 | article | {agent} | {"test": "test"} |
    When I send a DELETE request to "/api/v2/content_templates/{ct1}"
    And the response status code should be 200
    When I send a GET request to "{lastRequestUrl}"
    And the response status code should be 404

  Scenario: I try to delete, update and view content template that's not mine
    Given I'm authenticated as agent
    And "rival@deskprodemo.com" agent exists
    And I remove "agent" usergroup relation "agent_all_perms"
    And I remove "agent" usergroup relation "agent_all_safe_perms"
    And I set permission "articles.use" = 1 for "registered" usergroup
    And only the following ContentTemplate records exist:
      | #   | Title            | Type    | Person                  | Template         |
      | ct1 | Test template #1 | article | {rival@deskprodemo.com} | {"test": "test"} |
    When I send a PUT request to "/api/v2/content_templates/{ct1}" with body:
    """
{
  "title": "Test content template"
}
    """

    Then the response status code should be 403
    When I send a DELETE request to "/api/v2/content_templates/{ct1}"
    And the response status code should be 403
    When I send a GET request to "{lastRequestUrl}"
    And the response status code should be 403
