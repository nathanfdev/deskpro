@new
Feature: /user_chats endpoint
  To check list filters

  Background:
    Given I'm authenticated as admin
    And agent and user exist
    And only the following Chat records exist:
      | #  | Subject | Status | Agent   | Person |
      | c1 | Chat1   | open   | {admin} | {user} |
      | c2 | Chat2   | ended  | {admin} | NULL   |
      | c3 | Chat3   | ended  | {agent} | NULL   |
      | c4 | Chat4   | open   | NULL    | {user} |
    And only the following LabelChatConversation records exist:
      | Chat | Label   |
      | {c1} | label_1 |
      | {c1} | label_2 |
      | {c2} | label_2 |
      | {c2} | label_3 |
      | {c4} | label_4 |
      | {c4} | label_5 |

  Scenario: I filter by admin
    When I send a GET request to "/api/v2/user_chats?agent={admin}&order_by=id&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    Then the JSON node "data[0].id" should be equal to "{c1}"
    Then the JSON node "data[1].id" should be equal to "{c2}"

  Scenario: I filter by agent
    When I send a GET request to "/api/v2/user_chats?agent={agent}&order_by=id&order_dir=asc"
    Then the JSON node "data" should have 1 element
    Then the JSON node "data[0].id" should be equal to "{c3}"

  Scenario: I filter by person
    When I send a GET request to "/api/v2/user_chats?person={user}&order_by=id&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    Then the JSON node "data[0].id" should be equal to "{c1}"
    Then the JSON node "data[1].id" should be equal to "{c4}"

  Scenario: I filter by labels
    When I send a GET request to "/api/v2/user_chats?label=label_2&order_by=id&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    Then the JSON node "data[0].id" should be equal to "{c1}"
    Then the JSON node "data[1].id" should be equal to "{c2}"

  Scenario: I filter by status 'open'
    When I send a GET request to "/api/v2/user_chats?status=open&order_by=id&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    Then the JSON node "data[0].id" should be equal to "{c1}"
    Then the JSON node "data[1].id" should be equal to "{c4}"

  Scenario: I filter by status 'ended'
    When I send a GET request to "/api/v2/user_chats?status=ended&order_by=id&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    Then the JSON node "data[0].id" should be equal to "{c2}"
    Then the JSON node "data[1].id" should be equal to "{c3}"
