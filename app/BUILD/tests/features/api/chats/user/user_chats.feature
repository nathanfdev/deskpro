@new
Feature: /user_chats endpoint
  To retrieve DeskPRO agents
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as agent
    And I have only default brand
    And I have permissions to use agent_chat
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Chat Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1               |
    And only the following Chat records exist:
      | #  | subject |
      | c1 | Chat1   |
      | c2 | Chat2   |

  Scenario: I retrieve chat list
    When I send a GET request to "/api/v2/user_chats"
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].subject" should be equal to "Chat2"
    And the JSON node "data[1].subject" should be equal to "Chat1"

  Scenario: I get chat
    When I send a GET request to "/api/v2/user_chats/{c1}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.subject" should be equal to "Chat1"
