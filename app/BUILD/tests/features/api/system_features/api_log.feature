@logs
Feature: Api should log any request

  Background:
    Given I install the api data set
    And the setting "api_log.enabled" is set to 1
    And my request is authenticated

  @reinstall
  Scenario: I send some request to API
    When I send a PUT request to "/api/v2/notify/heartbeat"
    And the response status code should be 202
    And the response should be in JSON
    And the header "X-DeskPRO-Request-ID" should match "#\d+-[a-zA-Z0-9]{30}#"
    And api log should appear in table

  Scenario: I send some request to API and provide an client request id with
    Given I add "X-DeskPRO-Client-Request-ID" header equal to "de_dupe_header"
    When I send a PUT request to "/api/v2/notify/heartbeat"
    Then the response status code should be 202
    And the response should be in JSON
    And the header "X-DeskPRO-Request-ID" should be equal to "de_dupe_header-c"
    And api log with "de_dupe_header-c" id should appear in table

  Scenario Outline: I send some request to API and provide duplicate client request id.
    Checking different duplicate modes.
    Given I add "X-DeskPRO-Client-Request-ID" header equal to "de_dupe_header"
    And I set duplcicate mode as <dup_mode>, failure mode as <fail_mode>, eager as 0 in request
    When I send a PUT request to "/api/v2/notify/heartbeat"
    Then the response status code should be <status>
    And the header "X-DeskPRO-Request-ID" should be equal to "de_dupe_header-c"

    Examples:
      | dup_mode | fail_mode | status |
      | fail     | skip       | 409   |
      | resend   | skip       | 202   |

  Scenario Outline: I send some request to API and provide duplicate client request id.
    Request is failed. Checking different modes.
    Given I add "X-DeskPRO-Client-Request-ID" header equal to "de_dupe_header_modes"
    And I set duplcicate mode as <dup_mode>, failure mode as <fail_mode>, eager as 0 in request
    When I send a POST request to "/api/v2/agent_chats"
    Then the response status code should be <status>
    And the header "X-DeskPRO-Request-ID" should be equal to "de_dupe_header_modes-c"

    Examples:
      | dup_mode | fail_mode | status |
      | fail     | skip       | 400   |
      | fail     | save       | 400   |
      | fail     | skip       | 409   |
      | resend   | skip       | 400   |

  Scenario Outline: I send some request to API and previous request was sent with eager mode, and still in progress.
    Given There is the eager log with <gen_id> to "/api/v2/notify/heartbeat"
    And I add "X-DeskPRO-Client-Request-ID" header equal to <id>
    And I set duplcicate mode as <dup_mode>, failure mode as <fail_mode>, eager as 0 in request
    When I send a PUT request to "/api/v2/notify/heartbeat"
    Then the response status code should be <status>
    And the header "X-DeskPRO-Request-ID" should be equal to <gen_id>

    Examples:
      | dup_mode | fail_mode | status |           id             |             gen_id         |
      | fail     | skip       | 423     | de_dupe_header_eager1  | "de_dupe_header_eager1-c"  |
      | fail     | save       | 423     | de_dupe_header_eager2  | "de_dupe_header_eager2-c"  |
      | resend   | skip       | 423     | de_dupe_header_eager3  | "de_dupe_header_eager3-c"  |
      | resend   | save       | 423     | de_dupe_header_eager4  | "de_dupe_header_eager4-c"  |
