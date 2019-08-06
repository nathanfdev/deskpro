Feature: Community
  Clicking around the community, starting from the homepage

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme
    And no "CommunityTopic" records exist
    And the "community" channel "Suggestion" exists with content titled "Example Topic"

  Scenario: I visit the Community from the homepage
    And I am on "/"
    When I follow "Community"
    Then I should be on "/community"
    And the response status code should be 200

  Scenario: I visit a community topic from the Community page
    And I am on "/community"
    When I follow "Example Topic"
    Then I should be on "/community/view/example-topic"
    And the response status code should be 200
