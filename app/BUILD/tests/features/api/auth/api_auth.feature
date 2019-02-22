@new
Feature: API Authentication
  In order to interact with the API
  As anyone
  I must authenticate and be able to see /api/v2/me

  Background:
    Given there are no "Session" records

  Scenario: I do not submit any auth credentials
    When I send a GET request to "/api/v2/me"
    And the response status code should be 401
    And the header "WWW-Authenticate" should be equal to 'session,token,key realm="DeskPRO API"'
    Then the JSON node "status" should be equal to 401
    Then the JSON node "code" should be equal to "unauthorized"
    And the JSON node "message" should be equal to "You must be authenticated to make this request."

  Scenario: I use an invalid agent session cookie
    When I add cookie named "dpsid-agent" equal to "something-invalid"
    And I send a GET request to "/api/v2/me"
    And the response status code should be 401
    And the header "WWW-Authenticate" should be equal to 'session,token,key realm="DeskPRO API"'
    Then the JSON node "status" should be equal to 401
    Then the JSON node "code" should be equal to "unauthorized"

  Scenario: I use a malformed Authorization header
    When I add Authorization header equal to "key 1:XYZ invalid"
    And I send a GET request to "/api/v2/me"
    And the response status code should be 401
    And the header "WWW-Authenticate" should be equal to 'session,token,key realm="DeskPRO API"'
    Then the JSON node "status" should be equal to 401
    Then the JSON node "code" should be equal to "malformed_authorization_header"
    And the JSON node "message" should be equal to 'Malformed Authorization header (should be "Authorization: type value").'

  Scenario: I use an invalid Authorization header type
    When I add Authorization header equal to "invalid-type 1:XYZ"
    And I send a GET request to "/api/v2/me"
    And the response status code should be 401
    And the header "WWW-Authenticate" should be equal to 'session,token,key realm="DeskPRO API"'
    Then the JSON node "status" should be equal to 401
    Then the JSON node "code" should be equal to "invalid_authorization_header"
    And the JSON node "message" should be equal to 'Invalid Authorization header (type can be one of "key" or "token").'

  Scenario: I have a well-formed, but invalid api key
    When I add Authorization header equal to "key 91:XdfadfadfeYZ"
    And I send a GET request to "/api/v2/me"
    And the response status code should be 401
    And the header "WWW-Authenticate" should be equal to 'session,token,key realm="DeskPRO API"'
    Then the JSON node "status" should be equal to 401
    Then the JSON node "code" should be equal to "invalid_api_key"
    And the JSON node "message" should be equal to "Invalid API key."

  Scenario: I have a valid agent session ID
    Given "smith@deskpro.dev" agent exists
    And the agent session auth "HJKLOP" is valid for "smith@deskpro.dev" and referenced as "smithSession"
    When I add session cookie named "dpsid-agent" for session "smithSession"
    And I send a GET request to "/api/v2/me"
    Then the response status code should be 200
    And the JSON node "data.auth_method" should be equal to "agent_session"
    And the JSON node "data.person_id" should be equal to "{smith@deskpro.dev}"
    And I should have an authenticated token with the role ROLE_API

  Scenario: I have a valid agent session ID and it's an app request via X-DeskPRO-App-ID header
    Given "smith@deskpro.dev" agent exists
    And the agent session auth "HJKLOP" is valid for "smith@deskpro.dev" and referenced as "smithSession"
    When I add session cookie named "dpsid-agent" for session "smithSession"
    And I add "X-DeskPRO-App-ID" header equal to "12"
    And I send a GET request to "/api/v2/me"
    Then the response status code should be 200
    And the JSON node "data.app_id" should be equal to "12"

  Scenario: I have a valid session ID but I am NOT an agent
    Given "user@deskpro.dev" user exists
    And the agent session auth "UZER" is valid for "user@deskpro.dev" and referenced as "userSession"
    When I add session cookie named "dpsid-agent" for session "userSession"
    And I send a GET request to "/api/v2/me"
    And the response status code should be 401
    And the header "WWW-Authenticate" should be equal to 'session,token,key realm="DeskPRO API"'
    Then the JSON node "status" should be equal to 401
    Then the JSON node "code" should be equal to "invalid_session_id"
    And the JSON node "message" should be equal to "Invalid session ID."

  @skip-ci
  # test is skipped, api is for agents only for now
  Scenario: I have a valid api key (user "user" id=3 in the "api" data set)
    Given a valid api key exists with the code "XYZ" for user
    When I add Authorization header of my Api Key
    And I send a GET request to "/api/v2/me"
    Then the response status code should be 200
    And the JSON node "data.auth_method" should be equal to "api_key"
    And the JSON node "data.person_id" should be equal to 3
    And the JSON node "data.person.id" should be equal to 3
    And the JSON node "data.api_version" should be equal to 2
    And the JSON node "data.client_type" should be equal to "standard"
    And the JSON node "data.client_version" should be equal to 0
    And I should have an authenticated token with the role ROLE_API

  Scenario: Auth fails if ApiKey has no flag api_v2
    Given there are no User records
    And "smith@deskpro.dev" agent exists
    And a valid api key exists with the code "XYZ123" for agent
    When I add Authorization header of my Api Key
    And I send a GET request to "/api/v2/me"
    Then the response status code should be 200

    When I remove a flag 'api_v2' from ApiKey
    And I add Authorization header of my Api Key
    And I send a GET request to "/api/v2/me"
    Then the response status code should be 401
    Then the JSON node "status" should be equal to 401
    Then the JSON node "code" should be equal to "invalid_api_key"

  Scenario: I have a valid api token (user "agent" id=2 in the "api" data set)
    Given "smith@deskpro.dev" agent exists
    And there are no "ApiToken" records
    And a valid api token with the code "SECRETCODE" for "smith@deskpro.dev" and referenced as "token" exists
    When I add Authorization header equal to "token {token}:SECRETCODE"
    And I send a GET request to "/api/v2/me"
    Then the response status code should be 200
    And the JSON node "data.auth_method" should be equal to "api_token"
    And the JSON node "data.person_id" should be equal to "{smith@deskpro.dev}"
    And I should have an authenticated token with the role ROLE_API

  Scenario: I have an invalid api token
    When I add Authorization header equal to "token 1:incorrect"
    And I send a GET request to "/api/v2/me"
    And the response status code should be 401
    And the header "WWW-Authenticate" should be equal to 'session,token,key realm="DeskPRO API"'
    Then the JSON node "status" should be equal to 401
    Then the JSON node "code" should be equal to "invalid_api_token"
    And the JSON node "message" should be equal to "Invalid API token."
