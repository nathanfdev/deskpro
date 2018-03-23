@new @custom-fields
Feature: Check custom field pre set values

  Background:
    Given I'm authenticated as user

  Scenario: I check text field
    Given only the following custom person fields exist:
      | #          | Type | Title      |
      | text_field | text | Text field |
    And the object "user" has "text_field" custom data set to "some text"

    When I go to "/profile"
    Then the "person_profile[{text_field}][data]" field should contain "some text"

  Scenario: I check textarea field
    Given only the following custom person fields exist:
      | #              | Type | Title      |
      | textarea_field | textarea | Textarea field |
    And the object "user" has "textarea_field" custom data set to "more text"

    When I go to "/profile"
    Then the "person_profile[{textarea_field}][data]" field should contain "more text"

  Scenario: I check hidden field
    Given only the following custom person fields exist:
      | #            | Type   | Title        |
      | hidden_field | hidden | Hidden field |
    And the object "user" has "hidden_field" custom data set to "value"

    When I go to "/profile"
    Then the "person_profile[{hidden_field}][data]" hidden field should contain "value"

  Scenario: I check toggle field
    Given only the following custom person fields exist:
      | #              | Type   | Title        |
      | toggle_field_1 | toggle | Toggle field |
      | toggle_field_2 | toggle | Toggle field |
    And the object "user" has "toggle_field_1" custom data set to 1
    And the object "user" has "toggle_field_2" custom data set to 0

    When I go to "/profile"
    Then the "person_profile[{toggle_field_1}][data]" checkbox should be checked
    Then the "person_profile[{toggle_field_2}][data]" checkbox should not be checked

  Scenario: I check single selectbox
    Given only the following custom person fields exist:
      | #                   | Type          | Title        | Parent                |
      | single_choice_field | single_choice | Choice field |                       |
      | single_choice_1     |               | Choice 1     | {single_choice_field} |
      | single_choice_2     |               | Choice 2     | {single_choice_field} |
      | single_choice_3     |               | Choice 3     | {single_choice_field} |
    And the object "user" has "single_choice_field" custom data set to "{single_choice_2}"

    When I go to "/profile"
    Then the "person_profile[{single_choice_field}][data]" field should contain "{single_choice_2}"

  Scenario: I check checkbox group
    Given only the following custom person fields exist:
      | #                    | Type           | Title        | Parent                 |
      | checkbox_group_field | checkbox_group | Choice field |                        |
      | choice_1             |                | Choice 1     | {checkbox_group_field} |
      | choice_2             |                | Choice 2     | {checkbox_group_field} |
      | choice_3             |                | Choice 3     | {checkbox_group_field} |
    And the object "user" has "checkbox_group_field" custom data set to "{choice_2},{choice_3}"

    When I go to "/profile"
    Then the "person_profile_{checkbox_group_field}_data_0" checkbox should not be checked
    Then the "person_profile_{checkbox_group_field}_data_1" checkbox should be checked
    Then the "person_profile_{checkbox_group_field}_data_2" checkbox should be checked

  Scenario: I check radio group
    Given only the following custom person fields exist:
      | #                 | Type        | Title        | Parent              |
      | radio_group_field | radio_group | Choice field |                     |
      | choice_1          |             | Choice 1     | {radio_group_field} |
      | choice_2          |             | Choice 2     | {radio_group_field} |
      | choice_3          |             | Choice 3     | {radio_group_field} |
    And the object "user" has "radio_group_field" custom data set to "{choice_2}"

    When I go to "/profile"
    Then the "person_profile[{radio_group_field}][data]" field should contain "{choice_2}"

  Scenario: I check multi selectbox
    Given only the following custom person fields exist:
      | #                  | Type         | Title        | Parent               |
      | multi_choice_field | multi_choice | Choice field |                      |
      | choice_1           |              | Choice 1     | {multi_choice_field} |
      | choice_2           |              | Choice 2     | {multi_choice_field} |
      | choice_3           |              | Choice 3     | {multi_choice_field} |
    And the object "user" has "multi_choice_field" custom data set to "{choice_2},{choice_3}"

    When I go to "/profile"
    Then the "person_profile[{multi_choice_field}][data][]" multiple field should contain "{choice_2},{choice_3}"

  Scenario: I check date field
    Given only the following custom person fields exist:
      | #          | Type | Title      |
      | date_field | date | Date field |
    And the object "user" has "date_field" custom data set to "2016-06-15"

    When I go to "/profile"
    Then the "person_profile[{date_field}][data][year]" multiple field should contain 2016
    Then the "person_profile[{date_field}][data][month]" multiple field should contain 6
    Then the "person_profile[{date_field}][data][day]" multiple field should contain 15

  Scenario: I check datetime field
    Given only the following custom person fields exist:
      | #              | Type     | Title      |
      | datetime_field | datetime | Date field |
    And the object "user" has "datetime_field" custom data set to "2016-06-15 20:30:00"

    When I go to "/profile"
    Then the "person_profile[{datetime_field}][data][date][year]" multiple field should contain 2016
    Then the "person_profile[{datetime_field}][data][date][month]" multiple field should contain 6
    Then the "person_profile[{datetime_field}][data][date][day]" multiple field should contain 15
    Then the "person_profile[{datetime_field}][data][time][hour]" multiple field should contain 20
    Then the "person_profile[{datetime_field}][data][time][minute]" multiple field should contain 30

  Scenario: I check url field
    Given only the following custom person fields exist:
      | #         | Type | Title     |
      | url_field | url  | Url field |
    And the object "user" has "url_field" custom data set to "http://domain.tld"

    When I go to "/profile"
    Then the "person_profile[{url_field}][data]" field should contain "http://domain.tld"

  Scenario: I check currency field
    Given only the following Currency records exist:
      | #  | Name          | Currency Code | Symbol |
      | c1 | US Dollar     | USD           | $      |
      | c2 | British Pound | GBP           | £      |
      | c2 | Euro          | EUR           | €      |
    And only the following custom person fields exist:
      | #              | Type     | Title          | Options                                 |
      | currency_field | currency | Currency field | {"required": true, "currency_id": ~c2~} |
    And the object "user" has "currency_field" custom data set to "1020"

    When I go to "/profile"
    Then the "person_profile[{currency_field}][data]" field should contain "10.20"
