@chats @counts
Feature: /user_chats/counts endpoint
  To retrieve DeskPRO agents
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I count chats without grouping
    When I send a GET request to "/api/v2/user_chats/counts"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 5
    And the JSON node "data.nested" should have 0 elements

  Scenario: I group by date created
    When I send a GET request to "/api/v2/user_chats/counts?group_by=date_created"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 5
    And the JSON node "data.nested" should have 5 elements

    And the JSON node "data.nested[0].id" should be equal to "2010-08-01"
    And the JSON node "data.nested[0].type" should be equal to "date_created"
    And the JSON node "data.nested[0].title" should be equal to "2010-08-01"
    And the JSON node "data.nested[0].count" should be equal to 1

    And the JSON node "data.nested[1].id" should be equal to "2011-08-02"
    And the JSON node "data.nested[1].type" should be equal to "date_created"
    And the JSON node "data.nested[1].title" should be equal to "2011-08-02"
    And the JSON node "data.nested[1].count" should be equal to 1

    And the JSON node "data.nested[2].id" should be equal to "2015-08-03"
    And the JSON node "data.nested[2].type" should be equal to "date_created"
    And the JSON node "data.nested[2].title" should be equal to "2015-08-03"
    And the JSON node "data.nested[2].count" should be equal to 1

    And the JSON node "data.nested[3].id" should be equal to "2015-08-04"
    And the JSON node "data.nested[3].type" should be equal to "date_created"
    And the JSON node "data.nested[3].title" should be equal to "2015-08-04"
    And the JSON node "data.nested[3].count" should be equal to 1

    And the JSON node "data.nested[4].id" should be equal to "2015-08-05"
    And the JSON node "data.nested[4].type" should be equal to "date_created"
    And the JSON node "data.nested[4].title" should be equal to "2015-08-05"
    And the JSON node "data.nested[4].count" should be equal to 1

  Scenario: I group by and filter date created
    When I send a GET request to "/api/v2/user_chats/counts?group_by=date_created&created_from=2015-08-03&created_to=2015-08-05klk"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 2
    And the JSON node "data.nested" should have 2 elements

    And the JSON node "data.nested[0].id" should be equal to "2015-08-03"
    And the JSON node "data.nested[0].count" should be equal to 1

    And the JSON node "data.nested[1].id" should be equal to "2015-08-04"
    And the JSON node "data.nested[1].count" should be equal to 1

  Scenario: I group by date period
    When I send a GET request to "/api/v2/user_chats/counts?group_by=date_period"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 5
    And the JSON node "data.nested" should have 1 element

    And the JSON node "data.nested[0].id" should be equal to 0
    And the JSON node "data.nested[0].type" should be equal to "date_period"
    And the JSON node "data.nested[0].title" should be equal to "ever"
    And the JSON node "data.nested[0].count" should be equal to 5

  Scenario: I group by agent
    When I send a GET request to "/api/v2/user_chats/counts?group_by=agent"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 5
    And the JSON node "data.nested" should have 2 elements

    And the JSON node "data.nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].type" should be equal to "agent"
    And the JSON node "data.nested[0].title" should be equal to "Link Admin"
    And the JSON node "data.nested[0].count" should be equal to 2

    And the JSON node "data.nested[1].id" should be equal to 2
    And the JSON node "data.nested[1].type" should be equal to "agent"
    And the JSON node "data.nested[1].title" should be equal to "Zelda Agent"
    And the JSON node "data.nested[1].count" should be equal to 3

  Scenario: I filter and group by agent
    When I send a GET request to "/api/v2/user_chats/counts?group_by=agent&agent=2"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 3
    And the JSON node "data.nested" should have 1 element

    And the JSON node "data.nested[0].id" should be equal to 2
    And the JSON node "data.nested[0].count" should be equal to 3

  Scenario: I group by department
    When I send a GET request to "/api/v2/user_chats/counts?group_by=department"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 5
    And the JSON node "data.nested" should have 2 elements

    And the JSON node "data.nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].type" should be equal to "department"
    And the JSON node "data.nested[0].title" should be equal to "sales"
    And the JSON node "data.nested[0].count" should be equal to 3

    And the JSON node "data.nested[1].id" should be equal to 2
    And the JSON node "data.nested[1].type" should be equal to "department"
    And the JSON node "data.nested[1].title" should be equal to "support"
    And the JSON node "data.nested[1].count" should be equal to 2

  Scenario: I filter and group by department
    When I send a GET request to "/api/v2/user_chats/counts?group_by=department&department=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 3
    And the JSON node "data.nested" should have 1 element

    And the JSON node "data.nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].count" should be equal to 3
