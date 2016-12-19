Feature: Generate URLs
  To let visitors navigate around the site as expected
  As a developer
  I need to make sure the URLs I am generating respect the active language and mode

  Background: Fresh db
    Given I install the fresh data set

  Scenario: I generate a url with all the defaults
    Given the following languages are enabled:
      | default |
    And default is the active language
    When I generate a url for "portal_downloads"
    Then the generated url should be "/downloads"

  Scenario:  I generate a url when viewing a default language page to the hompage
    Given the following languages are enabled:
      | default |
      | french  |
    And default is the active language
    When I generate a url for "portal_home"
    Then the generated url should be "/en"

  Scenario: I generate a url when viewing a default language page
    Given the following languages are enabled:
      | default |
      | french  |
    And default is the active language
    When I generate a url for "portal_downloads"
    Then the generated url should be "/en/downloads"

  Scenario: I generate a url when non-default language is active
    Given the following languages are enabled:
      | default |
      | french  |
    And french is the active language
    When I generate a url for "portal_downloads"
    Then the generated url should be "/fr/downloads"

  Scenario: I generate a url when mode is normal
    Given the following languages are enabled:
      | default |
    Given the active mode is normal
    When I generate a url for "portal_downloads"
    Then the generated url should be "/downloads"

  Scenario: I generate a url to the homepage when mode is admin
    Given the following languages are enabled:
      | default |
    Given the active mode is admin
    When I generate a url for "portal_home"
    Then the generated url should be "/admin-mode"

  Scenario: I generate a url when mode is admin
    Given the following languages are enabled:
      | default |
    Given the active mode is admin
    When I generate a url for "portal_downloads"
    Then the generated url should be "/admin-mode/downloads"

  Scenario: I generate a url when mode is admin AND default multi-language portal to homepage
    Given the following languages are enabled:
      | default |
      | french  |
    Given the active mode is admin
    And default is the active language
    When I generate a url for "portal_home"
    Then the generated url should be "/admin-mode/en"

  Scenario: I generate a url when mode is admin AND default multi-language portal
    Given the following languages are enabled:
      | default |
      | french  |
    Given the active mode is admin
    And default is the active language
    When I generate a url for "portal_downloads"
    Then the generated url should be "/admin-mode/en/downloads"

  Scenario: I generate a url when mode is admin AND using non-default language in multi-language portal
    Given the following languages are enabled:
      | default |
      | french  |
    Given the active mode is admin
    And french is the active language
    When I generate a url for "portal_downloads"
    Then the generated url should be "/admin-mode/fr/downloads"

  Scenario: Some routes should not be generated using the language nor the mode
    Given the following languages are enabled:
      | default |
      | french  |
    And the active mode is admin
    And french is the active language
    When I generate urls for:
      | route                 | params                              |
      | agent                 |                                     |
      | admin                 |                                     |
      | serve_blob            | blob_auth_id=foo&filename=bar       |
      | serve_default_picture | s=foo                               |
      | serve_blob_sizefit    | blob_auth_id=foo&filename=bar&s=goo |
    Then none of the generated urls should start with "/admin-mode/fr"
