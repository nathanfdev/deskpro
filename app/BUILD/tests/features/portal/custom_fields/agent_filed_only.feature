@new @custom-fields
Feature: Agent field only

  Background:
    Given only the following custom ticket fields exist:
      | #              | Type | Title        | Is Agent Field |
      | ticket_field_1 | text | Custom field | 1              |
      | ticket_field_2 | text | Custom field | 0              |
    And only the following custom person fields exist:
      | #            | Type | Title        | Is Agent Field |
      | user_field_1 | text | Custom field | 1              |
      | user_field_2 | text | Custom field | 0              |
    And the only default ticket layout exists with fields:
      | user_layout                   |
      | ticket_field_{ticket_field_1} |
      | ticket_field_{ticket_field_2} |
      | user_field_{user_field_1}     |
      | user_field_{user_field_2}     |
      | user_field_{user_field_2}     |

    Scenario: I check registration agent only field is hidden
      When I go to "/register"
      Then the ".form-ticket" form should have 6 elements
      And I should see ".form-ticket" form fields in following order:
        | name                                      |
        | person_registration[name]                 |
        | person_registration[primary_email][email] |
        | person_registration[password][password]   |
        | person_registration[password][confirm]    |
        | person_registration[timezone]             |
        | person_registration[{user_field_2}][data] |

    Scenario: I check profile agent only field is hidden
      Given I'm authenticated as user
      When I go to "/profile"
      Then the ".form-ticket" form should have 4 elements
      And I should see ".form-ticket" form fields in following order:
        | name                                 |
        | person_profile[name]                 |
        | person_profile[upload_picture]       |
        | person_profile[timezone]             |
        | person_profile[{user_field_2}][data] |

    Scenario: I check new ticket agent only field is hidden
      When I go to "/new-ticket"
      Then the ".form-ticket" form should have 9 elements
      And I should see ".form-ticket" form fields in following order:
        | name                                        |
        | ticket[ticket_field_{ticket_field_2}][data] |
        | ticket[user_field_{user_field_2}][data]     |
        | ticket[department]                          |
        | ticket[subject]                             |
        | ticket[message][message]                    |
        | ticket[message][format]                     |
        | ticket[person][user_name]                   |
        | ticket[person][user_email][email]           |
        | ticket[displayed_fields]                    |
