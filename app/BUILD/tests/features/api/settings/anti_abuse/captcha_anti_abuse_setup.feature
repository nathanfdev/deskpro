@new
Feature: Captcha Anti-Abuse Setup

  Background:
    Given I'm authenticated as "admin"

  # defalut configuration comes from config files shipped with deskpro, so I consider this is normal to know
  # out-of-the-box system state
  Scenario: I get default configuration
    When I send a GET request to "/api/v2/settings/anti_abuse/captcha"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.use_recaptcha2" should be equal to 0
    And the JSON node "data.recaptcha2_site_key" should be equal to 0
    And the JSON node "data.recaptcha2_secret_key" should be equal to 0
    And the JSON node "data.tickets" should be equal to 0
    And the JSON node "data.comments" should be equal to 0
    And the JSON node "data.feedback" should be equal to 0
    And the JSON node "data.register" should be equal to 0
    And the JSON node "data.sharing" should be equal to 0

  Scenario: I update configuration
    When I send a PUT request to "/api/v2/settings/anti_abuse/captcha" with body:
    """
{
  "use_recaptcha2": true,
  "recaptcha2_site_key": "some key",
  "recaptcha2_secret_key": "some key",
  "tickets": "everyone",
  "comments": "guests",
  "feedback": "everyone",
  "register": "guests",
  "sharing": "everyone"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/settings/anti_abuse/captcha"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.use_recaptcha2" should be equal to 1
    And the JSON node "data.recaptcha2_site_key" should be equal to "some key"
    And the JSON node "data.recaptcha2_secret_key" should be equal to "some key"
    And the JSON node "data.tickets" should be equal to "everyone"
    And the JSON node "data.comments" should be equal to "guests"
    And the JSON node "data.feedback" should be equal to "everyone"
    And the JSON node "data.register" should be equal to "guests"
    And the JSON node "data.sharing" should be equal to "everyone"

    Scenario: I check validation
      When I send a PUT request to "/api/v2/settings/anti_abuse/captcha" with body:
      """
{
  "tickets": "unknown"
}
      """
      Then the response status code should be 400
      And the JSON node "errors.fields.tickets.errors[0].code" should be equal to "bad_choice"
      And the JSON node "errors.fields.tickets.errors[0].message" should be equal to "One or more of the given values is invalid."
