Feature: API Authentication
  In order to interact with the API
  As anyone
  I must authenticate and be able to see /api/v2/me

  Background:
    Given I install the api data set

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
    Then the JSON node "code" should be equal to "invalid_session_id"
    And the JSON node "message" should be equal to "Invalid session ID."

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

  Scenario: I have a valid agent session ID (user "agent" id=2 in the "api" data set)
    Given the agent session auth "HJKLOP" is valid for agent
    When I add cookie named "dpsid-agent" equal to "1-HJKLOP"
    And I send a GET request to "/api/v2/me"
    Then the response status code should be 200
    And the JSON node "data.auth_method" should be equal to "agent_session"
    And the JSON node "data.person_id" should be equal to 2
    And I should have an authenticated token with the role ROLE_API

  Scenario: I have a valid agent session ID and it's an app request via X-DeskPRO-App-ID header
    Given the agent session auth "HJKLOP" is valid for agent
    When I add cookie named "dpsid-agent" equal to "1-HJKLOP"
    And I add "X-DeskPRO-App-ID" header equal to "12"
    And I send a GET request to "/api/v2/me"
    Then the response status code should be 200
    And the JSON node "data.app_id" should be equal to 12

  Scenario: I have a valid session ID but I am NOT an agent
    Given the agent session auth "UZER" is valid for user
    When I add cookie named "dpsid-agent" equal to "1-UZER"
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

  Scenario: I have a valid api token (user "agent" id=2 in the "api" data set)
    Given a valid api token exists with the code "SECRETCODE" and id 1 for agent
    When I add Authorization header equal to "token 1:SECRETCODE"
    And I send a GET request to "/api/v2/me"
    Then the response status code should be 200
    And the JSON node "data.auth_method" should be equal to "api_token"
    And the JSON node "data.person_id" should be equal to 2
    And I should have an authenticated token with the role ROLE_API

  Scenario: I have an invalid api token
    When I add Authorization header equal to "token 1:incorrect"
    And I send a GET request to "/api/v2/me"
    And the response status code should be 401
    And the header "WWW-Authenticate" should be equal to 'session,token,key realm="DeskPRO API"'
    Then the JSON node "status" should be equal to 401
    Then the JSON node "code" should be equal to "invalid_api_token"
    And the JSON node "message" should be equal to "Invalid API token."
