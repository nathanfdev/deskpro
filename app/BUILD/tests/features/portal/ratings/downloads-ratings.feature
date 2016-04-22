Feature: Downloads ratings
  Users rating downloads

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme

  @reinstall
  Scenario: I rate a download positively as a user
    Given I login with user credentials
    And the "download" category "General" exists with content titled "Example Download"
    And I am on "/downloads/files/example-download"
    When I follow "This page was helpful"
    And I press "Continue"
    Then I should be on "/downloads/files/example-download"
    And I should see a "success" flash message
    And I should see "You marked this page as helpful"

  Scenario: I rate a download negatively as a user
    Given I login with user credentials
    And I am on "/downloads/files/example-download"
    When I follow "This page was not helpful"
    And I press "Continue"
    Then I should be on "/downloads/files/example-download"
    And I should see a "success" flash message
    And I should see "You marked this page as unhelpful"

  Scenario: I rate a download positively as a GUEST
    Given I am on "/downloads/files/example-download"
    When I follow "This page was helpful"
    And I press "Continue"
    Then I should be on "/downloads/files/example-download"
    And I should see a "success" flash message
    And I should see "You marked this page as helpful"

  Scenario: I rate a download negatively as a GUEST
    Given I am on "/downloads/files/example-download"
    When I follow "This page was not helpful"
    And I press "Continue"
    Then I should be on "/downloads/files/example-download"
    And I should see a "success" flash message
    And I should see "You marked this page as unhelpful"
