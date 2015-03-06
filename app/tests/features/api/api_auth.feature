Feature: API Authentication
  In order to interact with the API
  As anyone
  I must authenticate and be able to see /api/v2/me

  Background:
    Given I install the api data set

  @reinstall
  Scenario: I do not submit any auth credentials
    When I send a GET request to "/api/v2/me"
    Then the response status code should be 401
    And the JSON node "code" should be equal to "401"
    And the JSON node "message" should be equal to "No authentication credentials were found in your request."
    And the header "WWW-Authenticate" should be equal to 'session,token,key realm="DeskPRO API"'

  @reinstall
  Scenario: I have a valid agent session ID (user "agent" id=2 in the "api" data set)
    Given the agent session auth "HJKLOP" is valid for agent
    And I add cookie named "dpsid-agent" equal to "1-HJKLOP"
    When I send a GET request to "/api/v2/me"
    Then the response status code should be 200
    And the JSON node "data.auth_method" should be equal to "agent_session"
    And the JSON node "data.person_id" should be equal to 2

  @reinstall
  Scenario: I have a valid api key (user "user" id=3 in the "api" data set)
    Given a valid api key exists with the code "XYZ" and id 1 for user
    And I add Authorize header equal to "key 1:XYZ"
    When I send a GET request to "/api/v2/me"
    Then the response status code should be 200
    And the JSON node "data.auth_method" should be equal to "api_key"
    And the JSON node "data.person_id" should be equal to 3