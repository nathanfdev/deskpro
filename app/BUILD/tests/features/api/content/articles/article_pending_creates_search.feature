@new
Feature: /article_pending_creates endpoint
  To retrieve DeskPRO ArticlePendingCreate
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "agent"
    And "agent2@deskpro.dev" agent exists
    And I set permission "articles.use" = 1 for "registered" usergroup
    And only the following "PendingArticle" records exist:
      | #   | comment | assigned_person      |
      | pa1 | article | {me}                 |
      | pa2 | article | {agent2@deskpro.dev} |

  Scenario: I retrieve list of ArticlePendingCreate
    When I send a GET request to "/api/v2/article_pending_creates"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "meta.pagination.total" should be equal to 2

  Scenario: I retrieve list of ArticlePendingCreate filtering by assigned_person
    When I send a GET request to "/api/v2/article_pending_creates?assigned_person={agent2@deskpro.dev}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 elements
    And the JSON node "meta.pagination.total" should be equal to 1
