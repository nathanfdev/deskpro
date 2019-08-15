@new
Feature: /community_custom_channels endpoint
  To retrieve info about custom community channels (custom_category)
  As an API user
  I want an endpoint for custom community channels

  Background:
    Given I'm authenticated as admin
    And only the following custom community fields exist:
      | #    | parent | app_id | sys_name | js_class | has_form_template | has_display_template | title   | description | handler_class                                           | options | is_user_enabled | is_enabled | display_order | default_value | is_agent_field |
      | cdf1 |        |        | chan     |          | 0                 | 0                    | Channel | Channel     | Application\\\DeskPRO\\\CustomFields\\\Handler\\\Choice |         | 1               | 1          | 0             |               | 1              |
      | cdf2 | {cdf1} |        |          |          | 0                 | 0                    | Windows |             |                                                         |         | 1               | 1          | 0             |               | 1              |
      | cdf3 | {cdf1} |        |          |          | 0                 | 0                    | Mac     |             |                                                         |         | 1               | 1          | 0             |               | 1              |
      | cdf4 | {cdf1} |        |          |          | 0                 | 0                    | Linux   |             |                                                         |         | 1               | 1          | 0             |               | 1              |

  Scenario: I GET all custom channels

    When I send a GET request to "/api/v2/community_custom_channels"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].title" should be equal to "Linux"
    And the JSON node "data[1].title" should be equal to "Mac"
    And the JSON node "data[2].title" should be equal to "Windows"
