@counts @publish-nav
Feature: /article_pending_create/counts endpoint
  To retrieve counts of DeskPRO ArticlePendingCreate
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I retrieve total count of ArticlePendingCreate
    When I send a GET request to "/api/v2/article_pending_create/counts"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 2

  Scenario: I retrieve count filtering by assigned_person
    When I send a GET request to "/api/v2/article_pending_create/counts?assigned_person=2"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 1

  Scenario: I try to retrieve counts filtering by not existing assigned_person
    When I send a GET request to "/api/v2/article_pending_create/counts?assigned_person=404"
    Then the response should be in JSON
    And the response status code should be 404

  Scenario: I try to retrieve counts filtering by not existing filter due_date
    When I send a GET request to "/api/v2/article_pending_create/counts?due_date=2015-01-01"
    Then the response should be in JSON
    And the response status code should be 400
