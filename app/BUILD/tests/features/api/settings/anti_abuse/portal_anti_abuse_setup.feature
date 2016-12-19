@new
Feature: Portal Anti-Abuse Setup

  Background:
    Given I'm authenticated as "admin"

  # defalut configuration comes from config files shipped with deskpro, so I consider this is normal to know
  # out-of-the-box system state
  Scenario: I get default configuration
    When I send a GET request to "/api/v2/settings/anti_abuse/portal"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.agent_rate_limit.login_settings.enabled" should be equal to 1
    And the JSON node "data.agent_rate_limit.login_settings.limit" should be equal to 5
    And the JSON node "data.agent_rate_limit.login_settings.time" should be equal to 15
    And the JSON node "data.agent_rate_limit.login_settings.response" should be equal to "lockout"
    And the JSON node "data.agent_rate_limit.login_settings.lockout_time" should be equal to 15

    And the JSON node "data.account_rate_limit.registration_settings.enabled" should be equal to 1
    And the JSON node "data.account_rate_limit.registration_settings.limit" should be equal to 3
    And the JSON node "data.account_rate_limit.registration_settings.time" should be equal to 15
    And the JSON node "data.account_rate_limit.registration_settings.response" should be equal to "captcha"

    And the JSON node "data.account_rate_limit.reset_password_settings.enabled" should be equal to 1
    And the JSON node "data.account_rate_limit.reset_password_settings.limit" should be equal to 3
    And the JSON node "data.account_rate_limit.reset_password_settings.time" should be equal to 15
    And the JSON node "data.account_rate_limit.reset_password_settings.response" should be equal to "captcha"

    And the JSON node "data.user_rate_limit.submit_ticket.enabled" should be equal to 1
    And the JSON node "data.user_rate_limit.submit_ticket.limit" should be equal to 3
    And the JSON node "data.user_rate_limit.submit_ticket.time" should be equal to 15
    And the JSON node "data.user_rate_limit.submit_ticket.response" should be equal to "captcha"

    And the JSON node "data.user_rate_limit.login_settings.enabled" should be equal to 1
    And the JSON node "data.user_rate_limit.login_settings.limit" should be equal to 6
    And the JSON node "data.user_rate_limit.login_settings.time" should be equal to 15
    And the JSON node "data.user_rate_limit.login_settings.response" should be equal to "lockout"
    And the JSON node "data.user_rate_limit.login_settings.lockout_time" should be equal to 15

    And the JSON node "data.user_rate_limit.submit_feedback.enabled" should be equal to 1
    And the JSON node "data.user_rate_limit.submit_feedback.limit" should be equal to 3
    And the JSON node "data.user_rate_limit.submit_feedback.time" should be equal to 15
    And the JSON node "data.user_rate_limit.submit_feedback.response" should be equal to "captcha"

    And the JSON node "data.user_rate_limit.submit_comment.enabled" should be equal to 1
    And the JSON node "data.user_rate_limit.submit_comment.limit" should be equal to 3
    And the JSON node "data.user_rate_limit.submit_comment.time" should be equal to 15
    And the JSON node "data.user_rate_limit.submit_comment.response" should be equal to "captcha"

    And the JSON node "data.user_rate_limit.upload_attachment.enabled" should be equal to 1
    And the JSON node "data.user_rate_limit.upload_attachment.limit" should be equal to 50
    And the JSON node "data.user_rate_limit.upload_attachment.time" should be equal to 15
    And the JSON node "data.user_rate_limit.upload_attachment.response" should be equal to "lockout"

    And the JSON node "data.user_rate_limit.share_content.enabled" should be equal to 1
    And the JSON node "data.user_rate_limit.share_content.limit" should be equal to 3
    And the JSON node "data.user_rate_limit.share_content.time" should be equal to 15
    And the JSON node "data.user_rate_limit.share_content.response" should be equal to "captcha"

    And the JSON node "data.guest_rate_limit.submit_ticket.enabled" should be equal to 1
    And the JSON node "data.guest_rate_limit.submit_ticket.limit" should be equal to 3
    And the JSON node "data.guest_rate_limit.submit_ticket.time" should be equal to 15
    And the JSON node "data.guest_rate_limit.submit_ticket.response" should be equal to "captcha"

    And the JSON node "data.guest_rate_limit.submit_feedback.enabled" should be equal to 1
    And the JSON node "data.guest_rate_limit.submit_feedback.limit" should be equal to 3
    And the JSON node "data.guest_rate_limit.submit_feedback.time" should be equal to 15
    And the JSON node "data.guest_rate_limit.submit_feedback.response" should be equal to "captcha"

    And the JSON node "data.guest_rate_limit.submit_comment.enabled" should be equal to 1
    And the JSON node "data.guest_rate_limit.submit_comment.limit" should be equal to 3
    And the JSON node "data.guest_rate_limit.submit_comment.time" should be equal to 15
    And the JSON node "data.guest_rate_limit.submit_comment.response" should be equal to "captcha"

    And the JSON node "data.guest_rate_limit.upload_attachment.enabled" should be equal to 1
    And the JSON node "data.guest_rate_limit.upload_attachment.limit" should be equal to 50
    And the JSON node "data.guest_rate_limit.upload_attachment.time" should be equal to 15
    And the JSON node "data.guest_rate_limit.upload_attachment.response" should be equal to "lockout"

    And the JSON node "data.guest_rate_limit.share_content.enabled" should be equal to 1
    And the JSON node "data.guest_rate_limit.share_content.limit" should be equal to 3
    And the JSON node "data.guest_rate_limit.share_content.time" should be equal to 15
    And the JSON node "data.guest_rate_limit.share_content.response" should be equal to "captcha"

    And the JSON node "data.guest_rate_limit.login_settings.enabled" should be equal to 1
    And the JSON node "data.guest_rate_limit.login_settings.limit" should be equal to 3
    And the JSON node "data.guest_rate_limit.login_settings.time" should be equal to 60g
    And the JSON node "data.guest_rate_limit.login_settings.response" should be equal to "captcha"

  Scenario: I update configuration
    When I send a PUT request to "/api/v2/settings/anti_abuse/portal" with body:
    """
{
  "account_rate_limit": {

    "registration_settings": {
        "enabled": 0,
        "limit": 5,
        "time": 17,
        "lockout_time": 10,
        "response": "lockout"
    },
    "reset_password_settings": {
        "enabled": 1,
        "limit": 6,
        "time": 18,
        "response": "captcha"
    }
  },
  "agent_rate_limit": {

    "login_settings": {
        "enabled": 1,
        "limit": 4,
        "time": 16,
        "lockout_time": 100,
        "response": "lockout"
    }
  },
  "user_rate_limit": {
    "login_settings": {
        "enabled": 1,
        "limit": 4,
        "time": 16,
        "response": "captcha"
    },
    "submit_ticket": {
        "enabled": 0,
        "limit": 7,
        "time": 19,
        "lockout_time": 11,
        "response": "lockout"
    },
    "submit_feedback": {
        "enabled": 1,
        "limit": 8,
        "time": 20,
        "response": "captcha"
    },
    "submit_comment": {
        "enabled": 0,
        "limit": 9,
        "time": 21,
        "lockout_time": 12,
        "response": "lockout"
    },
    "upload_attachment": {
        "enabled": 1,
        "limit": 51,
        "time": 22,
        "response": "captcha"
    },
    "share_content": {
        "enabled": 1,
        "limit": 37,
        "time": 32,
        "response": "captcha"
    }
  },
  "guest_rate_limit": {
    "login_settings": {
        "enabled": 1,
        "limit": 4,
        "time": 16,
        "response": "captcha"
    },
    "submit_ticket": {
        "enabled": 0,
        "limit": 10,
        "time": 23,
        "lockout_time": 13,
        "response": "lockout"
    },
    "submit_feedback": {
        "enabled": 1,
        "limit": 11,
        "time": 24,
        "response": "captcha"
    },
    "submit_comment": {
        "enabled": 0,
        "limit": 12,
        "time": 25,
        "lockout_time": 14,
        "response": "lockout"
    },
    "upload_attachment": {
        "enabled": 1,
        "limit": 52,
        "time": 26,
        "response": "captcha"
    },
    "share_content": {
        "enabled": 1,
        "limit": 38,
        "time": 20,
        "response": "captcha"
    }
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/settings/anti_abuse/portal"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.agent_rate_limit.login_settings.enabled" should be equal to 1
    And the JSON node "data.agent_rate_limit.login_settings.limit" should be equal to 4
    And the JSON node "data.agent_rate_limit.login_settings.time" should be equal to 16
    And the JSON node "data.agent_rate_limit.login_settings.response" should be equal to "lockout"
    And the JSON node "data.agent_rate_limit.login_settings.lockout_time" should be equal to 100

    And the JSON node "data.account_rate_limit.registration_settings.enabled" should be equal to 0
    And the JSON node "data.account_rate_limit.registration_settings.limit" should be equal to 5
    And the JSON node "data.account_rate_limit.registration_settings.time" should be equal to 17
    And the JSON node "data.account_rate_limit.registration_settings.response" should be equal to "lockout"
    And the JSON node "data.account_rate_limit.registration_settings.lockout_time" should be equal to 10

    And the JSON node "data.account_rate_limit.reset_password_settings.enabled" should be equal to 1
    And the JSON node "data.account_rate_limit.reset_password_settings.limit" should be equal to 6
    And the JSON node "data.account_rate_limit.reset_password_settings.time" should be equal to 18
    And the JSON node "data.account_rate_limit.reset_password_settings.response" should be equal to "captcha"

    And the JSON node "data.user_rate_limit.login_settings.enabled" should be equal to 1
    And the JSON node "data.user_rate_limit.login_settings.limit" should be equal to 4
    And the JSON node "data.user_rate_limit.login_settings.time" should be equal to 16
    And the JSON node "data.user_rate_limit.login_settings.response" should be equal to "captcha"

    And the JSON node "data.user_rate_limit.submit_ticket.enabled" should be equal to 0
    And the JSON node "data.user_rate_limit.submit_ticket.limit" should be equal to 7
    And the JSON node "data.user_rate_limit.submit_ticket.time" should be equal to 19
    And the JSON node "data.user_rate_limit.submit_ticket.response" should be equal to "lockout"
    And the JSON node "data.user_rate_limit.submit_ticket.lockout_time" should be equal to 11

    And the JSON node "data.user_rate_limit.submit_feedback.enabled" should be equal to 1
    And the JSON node "data.user_rate_limit.submit_feedback.limit" should be equal to 8
    And the JSON node "data.user_rate_limit.submit_feedback.time" should be equal to 20
    And the JSON node "data.user_rate_limit.submit_feedback.response" should be equal to "captcha"

    And the JSON node "data.user_rate_limit.submit_comment.enabled" should be equal to 0
    And the JSON node "data.user_rate_limit.submit_comment.limit" should be equal to 9
    And the JSON node "data.user_rate_limit.submit_comment.time" should be equal to 21
    And the JSON node "data.user_rate_limit.submit_comment.response" should be equal to "lockout"
    And the JSON node "data.user_rate_limit.submit_comment.lockout_time" should be equal to 12

    And the JSON node "data.user_rate_limit.upload_attachment.enabled" should be equal to 1
    And the JSON node "data.user_rate_limit.upload_attachment.limit" should be equal to 51
    And the JSON node "data.user_rate_limit.upload_attachment.time" should be equal to 22
    And the JSON node "data.user_rate_limit.upload_attachment.response" should be equal to "captcha"

    And the JSON node "data.user_rate_limit.share_content.enabled" should be equal to 1
    And the JSON node "data.user_rate_limit.share_content.limit" should be equal to 37
    And the JSON node "data.user_rate_limit.share_content.time" should be equal to 32
    And the JSON node "data.user_rate_limit.share_content.response" should be equal to "captcha"

    And the JSON node "data.guest_rate_limit.login_settings.enabled" should be equal to 1
    And the JSON node "data.guest_rate_limit.login_settings.limit" should be equal to 4
    And the JSON node "data.guest_rate_limit.login_settings.time" should be equal to 16
    And the JSON node "data.guest_rate_limit.login_settings.response" should be equal to "captcha"

    And the JSON node "data.guest_rate_limit.submit_ticket.enabled" should be equal to 0
    And the JSON node "data.guest_rate_limit.submit_ticket.limit" should be equal to 10
    And the JSON node "data.guest_rate_limit.submit_ticket.time" should be equal to 23
    And the JSON node "data.guest_rate_limit.submit_ticket.response" should be equal to "lockout"
    And the JSON node "data.guest_rate_limit.submit_ticket.lockout_time" should be equal to 13

    And the JSON node "data.guest_rate_limit.submit_feedback.enabled" should be equal to 1
    And the JSON node "data.guest_rate_limit.submit_feedback.limit" should be equal to 11
    And the JSON node "data.guest_rate_limit.submit_feedback.time" should be equal to 24
    And the JSON node "data.guest_rate_limit.submit_feedback.response" should be equal to "captcha"

    And the JSON node "data.guest_rate_limit.submit_comment.enabled" should be equal to 0
    And the JSON node "data.guest_rate_limit.submit_comment.limit" should be equal to 12
    And the JSON node "data.guest_rate_limit.submit_comment.time" should be equal to 25
    And the JSON node "data.guest_rate_limit.submit_comment.response" should be equal to "lockout"
    And the JSON node "data.guest_rate_limit.submit_comment.lockout_time" should be equal to 14

    And the JSON node "data.guest_rate_limit.upload_attachment.enabled" should be equal to 1
    And the JSON node "data.guest_rate_limit.upload_attachment.limit" should be equal to 52
    And the JSON node "data.guest_rate_limit.upload_attachment.time" should be equal to 26
    And the JSON node "data.guest_rate_limit.upload_attachment.response" should be equal to "captcha"

    And the JSON node "data.guest_rate_limit.share_content.enabled" should be equal to 1
    And the JSON node "data.guest_rate_limit.share_content.limit" should be equal to 38
    And the JSON node "data.guest_rate_limit.share_content.time" should be equal to 20
    And the JSON node "data.guest_rate_limit.share_content.response" should be equal to "captcha"

  Scenario: I update configuration with bad data
    When I send a PUT request to "/api/v2/settings/anti_abuse/portal" with body:
    """
{
  "account_rate_limit": {
    "registration_settings": {
        "enabled": 0,
        "limit": 5,
        "time": 17,
        "response": "lockout"
    },
    "reset_password_settings": {
        "enabled": 1,
        "limit": 6,
        "time": 18,
        "response": "captcha"
    }
  },
  "user_rate_limit": {
    "login_settings": {
        "enabled": 1,
        "limit": 4,
        "time": 16,
        "response": "captcha"
    },
    "submit_ticket": {
        "enabled": 0,
        "limit": 7,
        "time": 19,
        "lockout_time": 11,
        "response": "lockout"
    },
    "submit_feedback": {
        "enabled": 1,
        "limit": 8,
        "time": 20,
        "response": "captcha"
    },
    "submit_comment": {
        "enabled": 0,
        "limit": 9,
        "time": 21,
        "lockout_time": 12,
        "response": "lockout"
    },
    "upload_attachment": {
        "enabled": 1,
        "limit": 51,
        "time": 22,
        "response": "captcha"
    },
    "share_content": {
        "enabled": 1,
        "limit": 37,
        "time": 32,
        "response": "captcha"
    }
  },
  "agent_rate_limit": {
    "login_settings": {
        "enabled": 1,
        "limit": 4,
        "time": 16,
        "response": "lockout"
    }
  },
  "guest_rate_limit": {
    "login_settings": {
        "enabled": 1,
        "limit": 4,
        "time": 16,
        "response": "lockout"
    },
    "submit_ticket": {
        "enabled": 0,
        "limit": 10,
        "time": 23,
        "lockout_time": 13,
        "response": "lockout"
    },
    "submit_feedback": {
        "enabled": 1,
        "limit": 11,
        "time": 24,
        "response": "captcha"
    },
    "submit_comment": {
        "enabled": 0,
        "limit": 12,
        "time": 25,
        "lockout_time": 14,
        "response": "lockout"
    },
    "upload_attachment": {
        "enabled": 1,
        "limit": 52,
        "time": 26,
        "response": "captcha"
    },
    "share_content": {
        "enabled": 1,
        "limit": 38,
        "time": 20,
        "response": "captcha"
    }
  }
}
    """
    Then the response status code should be 400