@new
Feature:
  To export filtered list of use chats to CSV file
  As an API user
  I need /user_chats/csv endpoint

  Background:
    Given I'm authenticated as agent
    And I have permissions to use feedback
    And only the following Chat records exist:
      | #  | Subject         |
      | c1 | Chat subject #1 |
      | c2 | Chat subject #2 |

  Scenario: I GET list of feedback in CSV format
    When I send a GET request to "/api/v2/user_chats/csv?count=200"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "data" should have 2 element
    And the JSON node "data[0].subject" should contain "Chat subject #"
