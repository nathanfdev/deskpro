Feature: Portal editor UI

  Background: Fresh database
    Given I install the fresh data set
    And I log in as admin
    And I am on "/admin/#/portal/portal_editor"

  Scenario: I see portal in the preview iframe
    Then I should see an "iframe" element
