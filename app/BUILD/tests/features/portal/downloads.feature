Feature: Downloads
  Clicking around the downloads, starting from the homepage

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme

  Scenario: I visit the Downloads from the homepage
    Given the "download" category "General" exists with content titled "Example Download"
    And I am on "/"
    When I follow "Downloads"
    Then I should be on "/downloads"
    And the response status code should be 200

  Scenario: I visit a category from the Downloads page
    Given I am on "/downloads"
    When I follow "General"
    Then I should be on "/downloads/general"
    And the response status code should be 200

  Scenario: I visit a download from the browse page
    Given I am on "/downloads/general"
    When I follow "Example Download"
    Then I should be on "/downloads/files/example-download"
    And the response status code should be 200
