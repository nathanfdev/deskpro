@new
Feature: CORS headers

  Background:
    Given there are no "Session" records

  Scenario: CORS disabled for not cross origin requests
    When I add Authorization header equal to "key 91:XdfadfadfeYZ"
    And I send a GET request to "/api/v2/me"
    Then the response status code should be 401
    And the header "Access-Control-Allow-Origin" should not exist

  Scenario: CORS disabled for malformed  API key
    When I add Authorization header equal to "key 91 XdfadfadfeYZ"
    And I add Origin header equal to "http://example.com"
    And I send a GET request to "/api/v2/me"
    Then the response status code should be 401
    And the header "Access-Control-Allow-Origin" should be equal to 'http://example.com'

  Scenario: CORS disabled for session auth type
    Given "smith@deskpro.dev" agent exists
    And the agent session auth "HJKLOP" is valid for "smith@deskpro.dev" and referenced as "smithSession"
    When I add session cookie named "dpsid-agent" for session "smithSession"
    And I add Origin header equal to "http://example.com"
    And I send a GET request to "/api/v2/me"
    Then the response status code should be 401
    And the header "Access-Control-Allow-Origin" should be equal to 'http://example.com'
    And the JSON node "code" should be equal to "invalid_cors_auth_type"

  Scenario: CORS disabled for not auth request
    When I add Origin header equal to "http://example.com"
    And I send a GET request to "/api/v2/me"
    Then the response status code should be 401
    And the header "Access-Control-Allow-Origin" should be equal to 'http://example.com'
    And the JSON node "code" should be equal to "unauthorized"

  Scenario: COSR disabled for not OAuth valid token
    Given "smith@deskpro.dev" agent exists
    And there are no "ApiToken" records
    And a valid api token with the code "SECRETCODE" for "smith@deskpro.dev" and referenced as "token" exists
    When I add Authorization header equal to "token {token}:SECRETCODE"
    And I add Origin header equal to "http://example.com"
    And I send a GET request to "/api/v2/me"
    Then the response status code should be 401
    And the header "Access-Control-Allow-Origin" should be equal to 'http://example.com'
    And the JSON node "code" should be equal to "invalid_cors_auth_type"

  Scenario: CORS enabled for invalid API key
    When I add Authorization header equal to "key 91:XdfadfadfeYZ"
    And I add Origin header equal to "http://example.com"
    And I send a GET request to "/api/v2/me"
    Then the response status code should be 401
    And the header "Access-Control-Allow-Origin" should be equal to 'http://example.com'

  Scenario: CORS enabled for invalid Token key
    When I add Authorization header equal to "token 91:XdfadfadfeYZ"
    And I add Origin header equal to "http://example.com"
    And I send a GET request to "/api/v2/me"
    Then the response status code should be 401
    And the header "Access-Control-Allow-Origin" should be equal to 'http://example.com'

  Scenario: CORS enabled for valid api key
    Given "smith@deskpro.dev" agent exists
    And a valid api key exists with the code "XYZ" for agent
    When I add Authorization header of my Api Key
    And I add Origin header equal to "http://example.com"
    And I send a GET request to "/api/v2/me"
    Then the response status code should be 200
    And the header "Access-Control-Allow-Origin" should be equal to 'http://example.com'

  Scenario: CORS enabled for valid api OAuth token
    Given only the following OAuthClient records exist:
      | #  | Name     | Context | Redirect Uris          |
      | c1 | Client 1 | Agent   | ["http://example.com"] |
    And "smith@deskpro.dev" agent exists
    And there are no "ApiToken" records
    And a valid api token for oauth client "c1" with the code "SECRETCODE" for "smith@deskpro.dev" and referenced as "token" exists
    When I add Authorization header equal to "token {token}:SECRETCODE"
    And I add Origin header equal to "http://example.com"
    And I send a GET request to "/api/v2/me"
    Then the response status code should be 200
    And the header "Access-Control-Allow-Origin" should be equal to 'http://example.com'

  Scenario: CORS enabled for preflight request
    When I add Origin header equal to "http://example.com"
    And I add "Access-Control-Request-Method" header equal to "GET"
    And I add "Access-Control-Request-Headers" header equal to "Authorization"
    And I send a OPTIONS request to "/api/v2/me"
    Then the response status code should be 200
    And the header "Access-Control-Allow-Origin" should be equal to 'http://example.com'
