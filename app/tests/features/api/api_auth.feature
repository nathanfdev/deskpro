Feature: API Authentication
  In order to interact with the API
  As anyone
  I must authenticate and be able to see /api/v2/me

  Background:
    Given I install the api data set

  @reinstall
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

  @reinstall
  Scenario: I have a valid agent session ID (user "agent" id=2 in the "api" data set)
    Given the agent session auth "HJKLOP" is valid for agent
    When I add cookie named "dpsid-agent" equal to "1-HJKLOP"
    And I send a GET request to "/api/v2/me"
    Then the response status code should be 200
    And the JSON node "data.auth_method" should be equal to "agent_session"
    And the JSON node "data.person_id" should be equal to 2
    And I should have an authenticated token with the role ROLE_API

  @reinstall
  Scenario: I have a valid agent session ID and it's an app request via X-DeskPRO-App-ID header
    Given the agent session auth "HJKLOP" is valid for agent
    When I add cookie named "dpsid-agent" equal to "1-HJKLOP"
    And I add "X-DeskPRO-App-ID" header equal to "12"
    And I send a GET request to "/api/v2/me"
    Then the response status code should be 200
    And the JSON node "data.app_id" should be equal to 12

  @reinstall
  Scenario: I have a valid session ID but I am NOT an agent
    Given the agent session auth "UZER" is valid for user
    When I add cookie named "dpsid-agent" equal to "1-UZER"
    And I send a GET request to "/api/v2/me"
    And the response status code should be 401
    And the header "WWW-Authenticate" should be equal to 'session,token,key realm="DeskPRO API"'
    Then the JSON node "status" should be equal to 401
    Then the JSON node "code" should be equal to "invalid_session_id"
    And the JSON node "message" should be equal to "Invalid session ID."

  @reinstall
  Scenario: I have a valid api key (user "user" id=3 in the "api" data set)
    Given a valid api key exists with the code "XYZ" and id 1 for user
    When I add Authorization header equal to "key 1:XYZ"
    And I send a GET request to "/api/v2/me"
    Then the response status code should be 200
    And the JSON node "data.auth_method" should be equal to "api_key"
    And the JSON node "data.person_id" should be equal to 3
    And I should have an authenticated token with the role ROLE_API

  Scenario: I fail to get a token because I make a bad request
    When I send a POST request to "/api/v2/api_tokens" with body:
    """
    {
    }
    """
    And the response status code should be 400
    And the JSON node "status" should be equal to 400
    And the JSON node "code" should be equal to "invalid_input"
    And the JSON node "message" should be equal to "Request input is invalid."
    And the JSON node "errors" should exist

  Scenario: I fail to get a token because I use the wrong credentials
    When I send a POST request to "/api/v2/api_tokens" with body:
    """
    {
      "email": "agent@deskpro.dev",
      "password": "wrong password"
    }
    """
    Then the response status code should be 401
    And the header "WWW-Authenticate" should be equal to 'session,token,key realm="DeskPRO API"'
    And the JSON node "status" should be equal to 401
    And the JSON node "code" should be equal to "bad_credentials"
    And the JSON node "message" should be equal to "Bad credentials."

  Scenario: I successfully get a token
    When I send a POST request to "/api/v2/api_tokens" with body:
    """
    {
      "email": "agent@deskpro.dev",
      "password": "password"
    }
    """
    Then the response status code should be 201
    And the JSON node "data.token" should exist

  @reinstall
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
