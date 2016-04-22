Feature: Subscriptions
  Users can subscribe to downloads and downloads categories

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme

  @reinstall
  Scenario: I subscribe to a download category successfully as a user
    Given the "download" category "General" exists with content titled "Example Download"
    And I login with user credentials
    And I am on "/downloads/general"
    When I follow "Subscribe"
    And I press "Continue"
    Then I should be on "/downloads/general"
    And user should be subscribed to the download category "General"
    And I should see a "success" flash message
    And I should see "Unsubscribe"

  Scenario: I subscribe to a download successfully as a user
    Given I login with user credentials
    And I am on "/downloads/files/example-download"
    When I follow "Subscribe"
    And I press "Continue"
    Then I should be on "/downloads/files/example-download"
    And user should be subscribed to the download content "Example Download"
    And I should see a "success" flash message

  Scenario: I cannot subscribe to a download category as a GUEST
    Given I am on "/downloads/general"
    When I follow "Subscribe"
    Then I should be on "/login"
    And the response status code should be 200

  Scenario: I cannot subscribe to a download as a GUEST
    Given I am on "/downloads/files/example-download"
    When I follow "Subscribe"
    Then I should be on "/login"
    And the response status code should be 200
