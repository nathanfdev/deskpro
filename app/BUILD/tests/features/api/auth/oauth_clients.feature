@new
Feature: /oauth_clients endpoint

  Background:
    Given I'm authenticated as admin
    And only the following OAuthClient records exist:
      | #  | Name     | Context | Redirect Uris          |
      | c1 | Client 1 | Agent   | ["http://example.com"] |

  Scenario: I get a list of oauth clients
    When I send a GET request to "api/v2/oauth_clients"
    Then the response status code should be 200
    And the JSON node "data[0].id" should be equal to "{c1}"
    And the JSON node "data[0].name" should be equal to "Client 1"
    And the JSON node "data[0].random_id" should exist
    And the JSON node "data[0].public_id" should exist
    And the JSON node "data[0].redirect_uris[0]" should be equal to "http://example.com"
    And the JSON node "data[0].allowed_grant_type" should be equal to "authorization_code"

  Scenario: I get a list of oauth client
    When I send a GET request to "api/v2/oauth_clients/{c1}"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to "{c1}"
    And the JSON node "data.name" should be equal to "Client 1"

  Scenario: I create an oauth client
    When I send a POST request to "api/v2/oauth_clients" with body:
    """
{
  "name": "My Client",
  "context": "agent",
  "redirect_uris": ["http://example-1.com/", "http://example-2.com/"]
}
    """
    Then the response status code should be 201
    And the JSON node "data.name" should be equal to "My Client"
    And the JSON node "data.redirect_uris[0]" should be equal to "http://example-1.com/"
    And the JSON node "data.redirect_uris[1]" should be equal to "http://example-2.com/"

  Scenario: I edit an existing oauth client
    When I send a PUT request to "api/v2/oauth_clients/{c1}" with body:
    """
{
  "name": "My Client",
  "redirect_uris": ["http://my-site-1.com/", "http://my-site-2.com/"]
}
    """
    Then the response status code should be 204

    When I send a GET request to "api/v2/oauth_clients/{c1}"
    And the JSON node "data.name" should be equal to "My Client"
    And the JSON node "data.redirect_uris[0]" should be equal to "http://my-site-1.com/"
    And the JSON node "data.redirect_uris[1]" should be equal to "http://my-site-2.com/"

  Scenario: I delete an existing oauth client
    When I send a DELETE request to "api/v2/oauth_clients/{c1}"
    Then the response status code should be 200

  Scenario: I check required name validation
    When I send a POST request to "api/v2/oauth_clients" with body:
    """
{
  "name": ""
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.name.errors[0].code" should be equal to "required"

  Scenario: I check required redirect_uris validation
    When I send a POST request to "api/v2/oauth_clients" with body:
    """
{
  "redirect_uris": []
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.redirect_uris.errors[0].code" should be equal to "too_few_elements"

  Scenario: I check redirect url validation
    When I send a POST request to "api/v2/oauth_clients" with body:
    """
{
  "redirect_uris": ["not_url"]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.redirect_uris.fields.redirect_uris_0.errors[0].code" should be equal to "invalid_url"
