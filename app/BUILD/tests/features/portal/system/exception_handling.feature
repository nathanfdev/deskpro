@new
Feature: Portal handles exceptions

  Background:
    Given I disable anti-abuse rate limiting
    And no Ticket records exist
    And the following languages are enabled:
      | default |

  Scenario: I check portal api access denied exception
    When I send a POST request to "/portal/api/chats/create?dpsid=AAAAAAAAAAAAAAA"
    Then the response status code should be 403
    And the JSON node "code" should be equal to 403

  Scenario: I check portal api bad request denied exception
    When I send a POST request to "/portal/api/chats/polling?dpsid=AAAAAAAAAAAAAAA"
    Then the response status code should be 404
    And the JSON node "code" should be equal to 404

  Scenario: I check portal access denied exception
    Given I set permission "tickets.use" = 0 for registered usergroup
    When I send a GET request to "/tickets/AAAA-BBBB-CCCC"
    Then the response status code should be 302

  Scenario: I check portal bad request denied exception
    Given I'm authenticated as user
    And I set permission "tickets.use" = 1 for registered usergroup
    When I send a GET request to "/tickets/AAAA-BBBB-CCCC"
    Then the response status code should be 404
