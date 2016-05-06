@articles
Feature: /article_pending_creates endpoint
  To retrieve DeskPRO ArticlePendingCreate
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated
    And I set permission "articles.use" = 1 for "registered" usergroup

  @reinstall
  Scenario: I retrieve list of ArticlePendingCreate
    When I send a GET request to "/api/v2/article_pending_creates"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "meta.pagination.total" should be equal to 2

  Scenario: I retrieve list of ArticlePendingCreate filtering by assigned_person
    When I send a GET request to "/api/v2/article_pending_creates?assigned_person=2"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 elements
    And the JSON node "meta.pagination.total" should be equal to 1
