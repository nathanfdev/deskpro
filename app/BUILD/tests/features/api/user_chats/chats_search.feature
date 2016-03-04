Feature: /user_chats endpoint search (GET)
  To retrieve various sets of DeskPRO chats
  As a developer
  I want an API endpoint with search capabilities

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I search for chats with empty search criteria
    When I send a GET request to "/api/v2/user_chats"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.count" should be equal to 5
    And the JSON node "meta.pagination.current_page" should be equal to 1
    And the JSON node "meta.pagination.total_pages" should be equal to 1
    And the JSON node "meta.pagination.total" should be equal to 5
    And the JSON node "data[0].subject" should be equal to "Test chat 1"

  Scenario: I search for chats sorting them by date_created in DESC order
    When I send a GET request to "/api/v2/user_chats?order_by=date_created&order_dir=desc"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta.pagination.count" should be equal to 5
    And the JSON node "data[0].subject" should be equal to "Test chat 5"
    And the JSON node "data[4].subject" should be equal to "Test chat 1"

  Scenario: I search for chats sorting them by date_created in ASC order
    When I send a GET request to "/api/v2/user_chats?order_by=date_created&order_dir=asc"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta.pagination.count" should be equal to 5
    And the JSON node "data[0].subject" should be equal to "Test chat 1"
    And the JSON node "data[4].subject" should be equal to "Test chat 5"

  Scenario: I search for chats sorting them by agent in DESC order
    When I send a GET request to "/api/v2/user_chats?order_by=agent&order_dir=desc"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta.pagination.count" should be equal to 5
    And the JSON node "data[0].agent" should be equal to "2"
    And the JSON node "data[4].agent" should be equal to "1"

  Scenario: I search for chats specifying date_created
    When I send a GET request to "/api/v2/user_chats?date_created=2015-08-01:2015-08-04"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.count" should be equal to 2
    And the JSON node "meta.pagination.current_page" should be equal to 1
    And the JSON node "meta.pagination.total_pages" should be equal to 1
    And the JSON node "meta.pagination.total" should be equal to 2
    And the JSON node "data[0].subject" should be equal to "Test chat 3"

  Scenario: I search for chats specifying date_period, agent and department
    When I send a GET request to "/api/v2/user_chats?date_period=ever&agent=1&department=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta.pagination" should exist

  Scenario: I get an empty collection when searching by not existing agent
    When I send a GET request to "/api/v2/user_chats?agent=404"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta.pagination.count" should be equal to 0
