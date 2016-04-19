Feature: Hiding internal actions
  In order to secure internal actions used for production mode testing
  As a developer
  I want to forbid accessing them by guests and users

  Background:
    Given I install the fresh data set

  Scenario Outline: I access an API internal action as guest
    When I go to "<internal_action>"
    Then the response status code should be 401

    Examples:
      | internal_action                                                                   |
      | /api/v2/_internal/incidents-demo/http-exception?confirm=Yes_I_use_it_for_testing  |
      | /api/v2/_internal/incidents-demo/php-notice?confirm=Yes_I_use_it_for_testing      |
      | /api/v2/_internal/incidents-demo/php-fatal-error?confirm=Yes_I_use_it_for_testing |
      | /api/v2/_internal/incidents-demo/exception?confirm=Yes_I_use_it_for_testing       |

  Scenario Outline: I access an API internal action as a registered user
    Given I log in as user from the portal
    When I go to "<internal_action>"
    Then the response status code should be 401

    Examples:
      | internal_action                                                                   |
      | /api/v2/_internal/incidents-demo/http-exception?confirm=Yes_I_use_it_for_testing  |
      | /api/v2/_internal/incidents-demo/php-notice?confirm=Yes_I_use_it_for_testing      |
      | /api/v2/_internal/incidents-demo/php-fatal-error?confirm=Yes_I_use_it_for_testing |
      | /api/v2/_internal/incidents-demo/exception?confirm=Yes_I_use_it_for_testing       |

  Scenario Outline: I access a portal internal action as guest
    When I go to "<internal_action>"
    Then I should be on "/login"

    Examples:
      | internal_action                                                                   |
      | /_internal/incidents-demo/http-exception?confirm=Yes_I_use_it_for_testing         |
      | /_internal/incidents-demo/php-notice?confirm=Yes_I_use_it_for_testing             |
      | /_internal/incidents-demo/php-fatal-error?confirm=Yes_I_use_it_for_testing        |
      | /_internal/incidents-demo/exception?confirm=Yes_I_use_it_for_testing              |

  Scenario Outline: I access a portal internal action as a registered user
    Given I log in as user from the portal
    When I go to "<internal_action>"
    Then the response status code should be 403

    Examples:
      | internal_action                                                                   |
      | /_internal/incidents-demo/http-exception?confirm=Yes_I_use_it_for_testing         |
      | /_internal/incidents-demo/php-notice?confirm=Yes_I_use_it_for_testing             |
      | /_internal/incidents-demo/php-fatal-error?confirm=Yes_I_use_it_for_testing        |
      | /_internal/incidents-demo/exception?confirm=Yes_I_use_it_for_testing              |
