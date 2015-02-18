Feature: Portal Router
  In order to manage mode and make sure users arent going to language urlsß
  As a developer
  I need a router that can match urls and redirect invalid ones

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |

  Scenario: I visit homepage and don't get redirected
    When I go to "/"
    Then I should be on "/"
    And the portal should be in "normal" mode

  Scenario: I visit another page and still don't get redirected
    When I go to "/new-ticket"
    Then I should be on "/new-ticket"

  Scenario: I visit a url with a mode
    When I go to "/admin-mode"
    Then I should be on "/admin-mode"
    And the portal should be in "admin" mode