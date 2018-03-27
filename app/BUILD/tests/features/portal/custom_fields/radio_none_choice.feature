@new @custom-fields
Feature: Option to reset radio field

  Background:
    Given I'm authenticated as user

  Scenario: I check the None Choice is present on the form
    Given only the following custom person fields exist:
      | #  | Type        | Title        | Parent | Options                |
      | f1 | radio_group | Choice field |        | {"none_choice": true } |
      | c1 |             | Choice 1     | {f1}   |                        |
      | c2 |             | Choice 2     | {f1}   |                        |
      | c3 |             | Choice 3     | {f1}   |                        |

    When I go to "/profile"
    Then I should see the "person_profile_{f1}_data_0" field
    And I should see the "person_profile_{f1}_data_1" field
    And I should see the "person_profile_{f1}_data_2" field
    And I should see the "person_profile_{f1}_data_3" field

  Scenario: I check the None Choice is NOT present on the form
    Given only the following custom person fields exist:
      | #  | Type        | Title        | Parent | Options                 |
      | f1 | radio_group | Choice field |        | {"none_choice": false } |
      | c1 |             | Choice 1     | {f1}   |                         |
      | c2 |             | Choice 2     | {f1}   |                         |
      | c3 |             | Choice 3     | {f1}   |                         |

    When I go to "/profile"
    Then I should see the "person_profile_{f1}_data_0" field
    And I should see the "person_profile_{f1}_data_1" field
    And I should see the "person_profile_{f1}_data_2" field
    And I should not see the "person_profile_{f1}_data_3" field

  Scenario: I check default title of the None choice
    Given only the following custom person fields exist:
      | #  | Type        | Title        | Parent | Options                |
      | f1 | radio_group | Choice field |        | {"none_choice": true } |
      | c1 |             | Choice 1     | {f1}   |                        |
      | c2 |             | Choice 2     | {f1}   |                        |
      | c3 |             | Choice 3     | {f1}   |                        |

    When I go to "/profile"
    Then the ".dp-choice-widget-group" element should contain "None"

  Scenario: I check default title override of the None choice
    Given only the following custom person fields exist:
      | #  | Type        | Title        | Parent | Options                                                    |
      | f1 | radio_group | Choice field |        | {"none_choice": true, "none_choice_title": "Empty option"} |
      | c1 |             | Choice 1     | {f1}   |                                                            |
      | c2 |             | Choice 2     | {f1}   |                                                            |
      | c3 |             | Choice 3     | {f1}   |                                                            |

    When I go to "/profile"
    Then the ".dp-choice-widget-group" element should contain "Empty option"

  Scenario: I check the None option has no value
    Given only the following custom person fields exist:
      | #  | Type        | Title        | Parent | Options                |
      | f1 | radio_group | Choice field |        | {"none_choice": true } |
      | c1 |             | Choice 1     | {f1}   |                        |
      | c2 |             | Choice 2     | {f1}   |                        |
      | c3 |             | Choice 3     | {f1}   |                        |

    When I go to "/profile"
    Then the response should contain "<input type=\"radio\" id=\"person_profile_{f1}_data_3\" name=\"person_profile[{f1}][data]\" value=\"\" checked=\"checked\" />"

  Scenario: I check no extra empty option was created after form submission
    Given only the following custom person fields exist:
      | #  | Type        | Title        | Parent | Options                |
      | f1 | radio_group | Choice field |        | {"none_choice": true } |
      | c1 |             | Choice 1     | {f1}   |                        |
      | c2 |             | Choice 2     | {f1}   |                        |
      | c3 |             | Choice 3     | {f1}   |                        |

    When I go to "/profile"
    And I fill in "person_profile[{f1}][data]" with "{c1}"
    And I press "Save"

    And I fill in "person_profile[{f1}][data]" with ""
    And I press "Save"

    When I go to "/profile"
    Then I should see the "person_profile_{f1}_data_0" field
    And I should see the "person_profile_{f1}_data_1" field
    And I should see the "person_profile_{f1}_data_2" field
    And I should see the "person_profile_{f1}_data_3" field
    And I should not see the "person_profile_{f1}_data_4" field
    And the response should contain "<input type=\"radio\" id=\"person_profile_{f1}_data_3\" name=\"person_profile[{f1}][data]\" value=\"\" checked=\"checked\" />"
