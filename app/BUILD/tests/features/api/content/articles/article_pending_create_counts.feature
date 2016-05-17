Feature: /article_pending_create/counts endpoint
  To retrieve counts of DeskPRO ArticlePendingCreate
  As an API user
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated
    And I set permission "articles.use" = 1 for "registered" usergroup

  Scenario: I retrieve total count of ArticlePendingCreate
    When I send a GET request to "/api/v2/article_pending_creates/counts"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 2

  Scenario: I retrieve count filtering by assigned_person
    When I send a GET request to "/api/v2/article_pending_creates/counts?assigned_person=2"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 1
