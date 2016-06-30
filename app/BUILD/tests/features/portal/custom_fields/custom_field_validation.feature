@new @custom-fields
Feature: Custom field validation

  Scenario Outline: I check required simple field
    Given only the following custom ticket fields exist:
      | # | Type   | Title        | Options            |
      | t | <type> | Custom field | {"required": true} |
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |

    When I go to "/new-ticket"
    And I press "Submit"
    Then "ticket[ticket_field_{t}]<property_path>" form field should have error with the phrase "This value is required"
    And "ticket[ticket_field_{t}]<property_path>" form field should have 1 error

    Examples:
      | type           | property_path      |
      | text           | [data]             |
      | textarea       | [data]             |
      | date           | [data][year]       |
      | datetime       | [data][date][year] |

  Scenario Outline: I check required choice field
    Given only the following custom ticket fields exist:
      | #  | Type   | Parent | Title        | Options            |
      | t  | <type> |        | Custom field | {"required": true} |
      | c1 |        | {t}    | Choice 1     |                    |
      | c2 |        | {t}    | Choice 2     |                    |
      | c3 |        | {t}    | Choice 3     |                    |
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |

    When I go to "/new-ticket"
    And I press "Submit"
    Then "ticket[ticket_field_{t}]<property_path>" form field should have error with the phrase "This value is required"
    And "ticket[ticket_field_{t}]<property_path>" form field should have 1 error

    Examples:
      | type           | property_path |
      | single_choice  | [data]        |
      | multi_choice   | [data][]      |
      | checkbox_group | [data][]      |
      | radio_group    | [data]        |

  Scenario Outline: I check too short text/textarea field
    Given only the following custom ticket fields exist:
      | # | Type   | Title        | Options           |
      | t | <type> | Custom field | {"min_length": 5} |
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |

    When I go to "/new-ticket"
    And I fill in "ticket[ticket_field_{t}][data]" with "123"
    And I press "Submit"
    Then "ticket[ticket_field_{t}][data]" form field should have error with the phrase "This value should have 5 characters or more"
    And "ticket[ticket_field_{t}][data]" form field should have 1 error

    Examples:
      | type     |
      | text     |
      | textarea |

  Scenario Outline: I check too long text/textarea field
    Given only the following custom ticket fields exist:
      | # | Type   | Title        | Options           |
      | t | <type> | Custom field | {"max_length": 5} |
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |

    When I go to "/new-ticket"
    And I fill in "ticket[ticket_field_{t}][data]" with "123456"
    And I press "Submit"
    Then "ticket[ticket_field_{t}][data]" form field should have error with the phrase "This value is too long. It should have 5 characters or less"
    And "ticket[ticket_field_{t}][data]" form field should have 1 error

    Examples:
      | type     |
      | text     |
      | textarea |

  Scenario: I check required toggle field
    Given only the following custom ticket fields exist:
      | # | Type   | Title        | Options                         |
      | t | toggle | Custom field | {"validation_type": "required"} |
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |

    Given I go to "/new-ticket"
    And I press "Submit"
    Then "ticket[ticket_field_{t}][data]" form field should have error with the phrase "The field is not checked."
    And "ticket[ticket_field_{t}][data]" form field should have 1 error
