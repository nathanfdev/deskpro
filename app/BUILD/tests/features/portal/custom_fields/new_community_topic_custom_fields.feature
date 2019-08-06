@new @custom-fields
Feature: New community topic form custom fields

  Background:
    Given I'm authenticated as user
    And no "CommunityTopic" records exist
    And only the following CommunityChannel records exist:
      | #  | Title      |
      | cc1 | Channel 1 |
      | cc2 | Channel 2 |
      | cc3 | Channel 3 |
    And I grant the "{cc1}" community channel permission for usergroup everyone
    And I grant the "{cc2}" community channel permission for usergroup everyone
    And I grant the "{cc3}" community channel permission for usergroup everyone
    And I set permission "community.use" = 1 for "everyone" usergroup

  Scenario: I check custom fields exist on the form
    Given only the following custom community fields exist:
      | #              | Type     | Title          |
      | text_field     | text     | Text field     |
      | textarea_field | textarea | Textarea field |

    When I go to "/community"
    Then I should see the "new_community_topic[custom_data][{text_field}][data]" field
    And I should see the "new_community_topic[custom_data][{textarea_field}][data]" field

  Scenario: I check custom field w/o validation
    Given only the following custom community fields exist:
      | #          | Type | Title      |
      | text_field | text | Text field |
    And I go to "/community"

    When I select "Channel 1" from "new_community_topic_channel"
    And I fill in "new_community_topic_title" with "Title"
    And I fill in "new_community_topic_content" with "I need to report the following bug. It happens when..."
    And I fill in "new_community_topic[custom_data][{text_field}][data]" with "12345"
    And I press "Add your community topic"
    Then I should not see a form error with the phrase "This value should have "

  Scenario: I check custom fields validation
    Given only the following custom community fields exist:
      | #          | Type | Title      | Options                              |
      | text_field | text | Text field | {"required": true, "min_length": 10} |
    And I go to "/community"

    When I select "Channel 2" from "new_community_topic_channel"
    And I fill in "new_community_topic_title" with "Title"
    And I fill in "new_community_topic_content" with "I need to report the following bug. It happens when..."
    And I fill in "new_community_topic[custom_data][{text_field}][data]" with "12345"
    And I press "Add your community topic"
    Then I should see a form error with the phrase "This value should have 10 characters or more"
