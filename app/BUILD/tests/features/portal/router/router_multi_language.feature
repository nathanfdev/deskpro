Feature: Multi Language Router
  In order to manage language state in URLs
  As a developer
  I need a router that can match urls and redirect invalid ones

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
      | french  |

  Scenario: I visit a multi lang site with no url code
    When I go to "/"
    Then I should be on "/en"

  Scenario: I visit a specific page with no url code
    When I go to "/register"
    Then I should be on "/en/register"

  Scenario: I visit a specific page with no url code and a mode is activated
    When I go to "/admin-mode/register"
    Then I should be on "/admin-mode/en/register"

  Scenario: I change language in URL
    Given I login with user credentials
    When I go to "/fr/news"
    Then I should be on "/fr/news"
