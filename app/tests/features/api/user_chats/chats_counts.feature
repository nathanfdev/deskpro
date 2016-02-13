@counts @chat-nav
Feature: /user_chats/counts endpoint
  To retrieve counts of various sets of DeskPRO chats
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I count for chats grouping them by agents
    When I send a GET request to "/api/v2/user_chats/counts?group_by=agent"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 5
    And the JSON node "data.grouped_by" should be equal to "agent"
    And the JSON node "data.nested" should have 2 elements

  Scenario: I count for chats grouping them by date_period
    When I send a GET request to "/api/v2/user_chats/counts?group_by=date_period"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 5
    And the JSON node "data.grouped_by" should be equal to "date_period"

  Scenario: I count for chats created between 2015-08-01 and 2015-08-04 grouping them by departments
    When I send a GET request to "/api/v2/user_chats/counts?group_by=department&date_created=2015-08-01:2015-08-05"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 3
    And the JSON node "data.grouped_by" should be equal to "department"
    And the JSON node "data.nested" should have 2 elements

  Scenario: I count for chats created during the year and grouping them by agents
    When I send a GET request to "/api/v2/user_chats/counts?group_by=agent&date_period=this_year"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.grouped_by" should be equal to "agent"
