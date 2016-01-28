Feature: Languages and Translation
  To offer a multi-language portal
  As a developer
  I need the request URL to set the active language on the language stack

  Background: Using a fresh data set
    Given I install the fresh data set

  Scenario: Multi lang site, visit default lang url
    Given the following languages are enabled:
      | default |
      | french  |
    When I go to "/en"
    Then "default" should be the active language

  Scenario: Single language gets set correctly
    Given the following languages are enabled:
      | default |
    When I go to "/"
    Then "default" should be the active language

  Scenario: Multi lang site, visit non-default lang
    Given the following languages are enabled:
      | default |
      | french |
    When I go to "/fr"
    Then "french" should be the active language

