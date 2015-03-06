Feature: API Authentication
  In order to interact with the API
  As anyone
  I must authenticate

  Background: Using a empty data set
    Given I install the empty data set

  Scenario: I do not submit any auth credentials
    When I send a GET request to "/api/v2/me"
    Then the response status code should be 401
    And the JSON node "code" should be equal to "401"
    And the JSON node "message" should be equal to "No authentication credentials found in the request"
    And the header "WWW-Authenticate" should contain "DeskPRO Api"