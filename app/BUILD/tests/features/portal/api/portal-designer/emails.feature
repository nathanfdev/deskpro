Feature: Search users by emails
  To preview portal changes on behalf of any user
  As a DeskPRO admin
  I want to search people by their emails

  Background: Fresh database
    Given I install the fresh data set

  Scenario: I search for users
    Given I'm authenticated as admin
    When I send a GET request to "/portal/api/emails?target=user&term=a"
    Then the response status code should be 200

  Scenario: I search for agents
    Given I'm authenticated as admin
    When I send a GET request to "/portal/api/emails?target=agent&term=a"
    Then the response status code should be 200
