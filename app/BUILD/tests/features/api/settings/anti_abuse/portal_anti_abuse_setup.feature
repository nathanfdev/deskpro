Feature: Portal Anti-Abuse Setup

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I get default configuration
    When I send a GET request to "/api/v2/settings/anti_abuse/portal"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.account_rate_limit.login_settings.enabled" should be equal to 0
    And the JSON node "data.account_rate_limit.login_settings.limit" should be equal to 3
    And the JSON node "data.account_rate_limit.login_settings.time" should be equal to 900
    And the JSON node "data.account_rate_limit.login_settings.response" should be equal to "captcha"

    And the JSON node "data.account_rate_limit.registration_settings.enabled" should be equal to 0
    And the JSON node "data.account_rate_limit.registration_settings.limit" should be equal to 3
    And the JSON node "data.account_rate_limit.registration_settings.time" should be equal to 900
    And the JSON node "data.account_rate_limit.registration_settings.response" should be equal to "captcha"

    And the JSON node "data.account_rate_limit.reset_password_settings.enabled" should be equal to 0
    And the JSON node "data.account_rate_limit.reset_password_settings.limit" should be equal to 3
    And the JSON node "data.account_rate_limit.reset_password_settings.time" should be equal to 900
    And the JSON node "data.account_rate_limit.reset_password_settings.response" should be equal to "captcha"

    And the JSON node "data.user_rate_limit.submit_ticket.enabled" should be equal to 0
    And the JSON node "data.user_rate_limit.submit_ticket.limit" should be equal to 3
    And the JSON node "data.user_rate_limit.submit_ticket.time" should be equal to 900
    And the JSON node "data.user_rate_limit.submit_ticket.response" should be equal to "captcha"

    And the JSON node "data.user_rate_limit.submit_feedback.enabled" should be equal to 0
    And the JSON node "data.user_rate_limit.submit_feedback.limit" should be equal to 3
    And the JSON node "data.user_rate_limit.submit_feedback.time" should be equal to 900
    And the JSON node "data.user_rate_limit.submit_feedback.response" should be equal to "captcha"

    And the JSON node "data.user_rate_limit.submit_comment.enabled" should be equal to 0
    And the JSON node "data.user_rate_limit.submit_comment.limit" should be equal to 3
    And the JSON node "data.user_rate_limit.submit_comment.time" should be equal to 900
    And the JSON node "data.user_rate_limit.submit_comment.response" should be equal to "captcha"

    And the JSON node "data.user_rate_limit.upload_attachment.enabled" should be equal to 0
    And the JSON node "data.user_rate_limit.upload_attachment.limit" should be equal to 50
    And the JSON node "data.user_rate_limit.upload_attachment.time" should be equal to 900
    And the JSON node "data.user_rate_limit.upload_attachment.response" should be equal to "lockout"

    And the JSON node "data.guest_rate_limit.submit_ticket.enabled" should be equal to 0
    And the JSON node "data.guest_rate_limit.submit_ticket.limit" should be equal to 3
    And the JSON node "data.guest_rate_limit.submit_ticket.time" should be equal to 900
    And the JSON node "data.guest_rate_limit.submit_ticket.response" should be equal to "captcha"

    And the JSON node "data.guest_rate_limit.submit_feedback.enabled" should be equal to 0
    And the JSON node "data.guest_rate_limit.submit_feedback.limit" should be equal to 3
    And the JSON node "data.guest_rate_limit.submit_feedback.time" should be equal to 900
    And the JSON node "data.guest_rate_limit.submit_feedback.response" should be equal to "captcha"

    And the JSON node "data.guest_rate_limit.submit_comment.enabled" should be equal to 0
    And the JSON node "data.guest_rate_limit.submit_comment.limit" should be equal to 3
    And the JSON node "data.guest_rate_limit.submit_comment.time" should be equal to 900
    And the JSON node "data.guest_rate_limit.submit_comment.response" should be equal to "captcha"

    And the JSON node "data.guest_rate_limit.upload_attachment.enabled" should be equal to 0
    And the JSON node "data.guest_rate_limit.upload_attachment.limit" should be equal to 50
    And the JSON node "data.guest_rate_limit.upload_attachment.time" should be equal to 900
    And the JSON node "data.guest_rate_limit.upload_attachment.response" should be equal to "lockout"

  Scenario: I update configuration
    When I send a PUT request to "/api/v2/settings/anti_abuse/portal" with body:
    """
{
  "account_rate_limit": {
    "login_settings": {
        "enabled": 1,
        "limit": 4,
        "time": 901,
        "response": "captcha"
    },
    "registration_settings": {
        "enabled": 0,
        "limit": 5,
        "time": 902,
        "response": "lockout"
    },
    "reset_password_settings": {
        "enabled": 1,
        "limit": 6,
        "time": 903,
        "response": "captcha"
    }
  },
  "user_rate_limit": {
    "submit_ticket": {
        "enabled": 0,
        "limit": 7,
        "time": 904,
        "response": "lockout"
    },
    "submit_feedback": {
        "enabled": 1,
        "limit": 8,
        "time": 905,
        "response": "captcha"
    },
    "submit_comment": {
        "enabled": 0,
        "limit": 9,
        "time": 906,
        "response": "lockout"
    },
    "upload_attachment": {
        "enabled": 1,
        "limit": 51,
        "time": 907,
        "response": "captcha"
    }
  },
  "guest_rate_limit": {
    "submit_ticket": {
        "enabled": 0,
        "limit": 10,
        "time": 908,
        "response": "lockout"
    },
    "submit_feedback": {
        "enabled": 1,
        "limit": 11,
        "time": 909,
        "response": "captcha"
    },
    "submit_comment": {
        "enabled": 0,
        "limit": 12,
        "time": 910,
        "response": "lockout"
    },
    "upload_attachment": {
        "enabled": 1,
        "limit": 52,
        "time": 910,
        "response": "captcha"
    }
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/settings/anti_abuse/portal"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.account_rate_limit.login_settings.enabled" should be equal to 1
    And the JSON node "data.account_rate_limit.login_settings.limit" should be equal to 4
    And the JSON node "data.account_rate_limit.login_settings.time" should be equal to 901
    And the JSON node "data.account_rate_limit.login_settings.response" should be equal to "captcha"

    And the JSON node "data.account_rate_limit.registration_settings.enabled" should be equal to 0
    And the JSON node "data.account_rate_limit.registration_settings.limit" should be equal to 5
    And the JSON node "data.account_rate_limit.registration_settings.time" should be equal to 902
    And the JSON node "data.account_rate_limit.registration_settings.response" should be equal to "lockout"

    And the JSON node "data.account_rate_limit.reset_password_settings.enabled" should be equal to 1
    And the JSON node "data.account_rate_limit.reset_password_settings.limit" should be equal to 6
    And the JSON node "data.account_rate_limit.reset_password_settings.time" should be equal to 903
    And the JSON node "data.account_rate_limit.reset_password_settings.response" should be equal to "captcha"

    And the JSON node "data.user_rate_limit.submit_ticket.enabled" should be equal to 0
    And the JSON node "data.user_rate_limit.submit_ticket.limit" should be equal to 7
    And the JSON node "data.user_rate_limit.submit_ticket.time" should be equal to 904
    And the JSON node "data.user_rate_limit.submit_ticket.response" should be equal to "lockout"

    And the JSON node "data.user_rate_limit.submit_feedback.enabled" should be equal to 1
    And the JSON node "data.user_rate_limit.submit_feedback.limit" should be equal to 8
    And the JSON node "data.user_rate_limit.submit_feedback.time" should be equal to 905
    And the JSON node "data.user_rate_limit.submit_feedback.response" should be equal to "captcha"

    And the JSON node "data.user_rate_limit.submit_comment.enabled" should be equal to 0
    And the JSON node "data.user_rate_limit.submit_comment.limit" should be equal to 9
    And the JSON node "data.user_rate_limit.submit_comment.time" should be equal to 906
    And the JSON node "data.user_rate_limit.submit_comment.response" should be equal to "lockout"

    And the JSON node "data.user_rate_limit.upload_attachment.enabled" should be equal to 1
    And the JSON node "data.user_rate_limit.upload_attachment.limit" should be equal to 51
    And the JSON node "data.user_rate_limit.upload_attachment.time" should be equal to 907
    And the JSON node "data.user_rate_limit.upload_attachment.response" should be equal to "captcha"

    And the JSON node "data.guest_rate_limit.submit_ticket.enabled" should be equal to 0
    And the JSON node "data.guest_rate_limit.submit_ticket.limit" should be equal to 10
    And the JSON node "data.guest_rate_limit.submit_ticket.time" should be equal to 908
    And the JSON node "data.guest_rate_limit.submit_ticket.response" should be equal to "lockout"

    And the JSON node "data.guest_rate_limit.submit_feedback.enabled" should be equal to 1
    And the JSON node "data.guest_rate_limit.submit_feedback.limit" should be equal to 11
    And the JSON node "data.guest_rate_limit.submit_feedback.time" should be equal to 909
    And the JSON node "data.guest_rate_limit.submit_feedback.response" should be equal to "captcha"

    And the JSON node "data.guest_rate_limit.submit_comment.enabled" should be equal to 0
    And the JSON node "data.guest_rate_limit.submit_comment.limit" should be equal to 12
    And the JSON node "data.guest_rate_limit.submit_comment.time" should be equal to 910
    And the JSON node "data.guest_rate_limit.submit_comment.response" should be equal to "lockout"

    And the JSON node "data.guest_rate_limit.upload_attachment.enabled" should be equal to 1
    And the JSON node "data.guest_rate_limit.upload_attachment.limit" should be equal to 52
    And the JSON node "data.guest_rate_limit.upload_attachment.time" should be equal to 910
    And the JSON node "data.guest_rate_limit.upload_attachment.response" should be equal to "captcha"
