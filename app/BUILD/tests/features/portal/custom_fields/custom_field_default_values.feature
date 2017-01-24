@new @custom-fields
Feature: Custom field default values

  Background:
    Given I'm authenticated as user
    And I have only default brand
    And I set permission "tickets.use" = 1 for "registered" usergroup
    And only the following Department records exist:
      | #  | Parent | Title          | Brands           | Is Tickets Enabled |
      | d1 | NULL   | Department 1   | [{defaultBrand}] | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup everyone

  Scenario Outline: I check text/textarea field's default value
    Given only the following custom ticket fields exist:
      | # | Type   | Title        | Default Value |
      | t | <type> | Custom field | some text     |
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |

    When I go to "/new-ticket"
    Then the "ticket[ticket_field_{t}][data]" field should contain "some text"

    Examples:
      | type     |
      | text     |
      | textarea |

  Scenario: I check hidden field's default value
    Given only the following custom ticket fields exist:
      | # | Type   | Title        | Default Value |
      | t | hidden | Custom field | some text     |
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |

    When I go to "/new-ticket"
    Then the "ticket[ticket_field_{t}][data]" hidden field should contain "some text"

  Scenario: I check toggle field's default value
    Given only the following custom ticket fields exist:
      | # | Type   | Title        | Default Value |
      | t | toggle | Custom field | 1             |
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |

    When I go to "/new-ticket"
    Then the "ticket[ticket_field_{t}][data]" checkbox should be checked

  Scenario: I check single selectbox field's default value
    Given only the following custom ticket fields exist:
      | #  | Type          | Title        | Parent |
      | t  | single_choice | Custom field |        |
      | c1 |               | Choice 1     | {t}    |
      | c2 |               | Choice 2     | {t}    |
      | c3 |               | Choice 3     | {t}    |
    And the "{t}" record "default value" prop is equal to "{c2:id}"
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |

    When I go to "/new-ticket"
    Then the "ticket[ticket_field_{t}][data]" field should contain "{c2}"

  Scenario: I check checkbox group's default value
    Given only the following custom ticket fields exist:
      | #  | Type           | Title        | Parent |
      | t  | checkbox_group | Custom field |        |
      | c1 |                | Choice 1     | {t}    |
      | c2 |                | Choice 2     | {t}    |
      | c3 |                | Choice 3     | {t}    |
    And the "{t}" record "default value" prop is equal to "{c2:id}, {c3:id}"
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |

    When I go to "/new-ticket"
    Then the "ticket_ticket_field_{t}_data_0" checkbox should not be checked
    And the "ticket_ticket_field_{t}_data_1" checkbox should be checked
    And the "ticket_ticket_field_{t}_data_2" checkbox should be checked

  Scenario: I check radio group's default value
    Given only the following custom ticket fields exist:
      | #  | Type        | Title        | Parent |
      | t  | radio_group | Custom field |        |
      | c1 |             | Choice 1     | {t}    |
      | c2 |             | Choice 2     | {t}    |
      | c3 |             | Choice 3     | {t}    |
    And the "{t}" record "default value" prop is equal to "{c2:id}"
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |

    When I go to "/new-ticket"
    Then the "ticket[ticket_field_{t}][data]" field should contain "{c2}"

  Scenario: I check multi selectbox's default value
    Given only the following custom ticket fields exist:
      | #  | Type         | Title        | Parent |
      | t  | multi_choice | Custom field |        |
      | c1 |              | Choice 1     | {t}    |
      | c2 |              | Choice 2     | {t}    |
      | c3 |              | Choice 3     | {t}    |
    And the "{t}" record "default value" prop is equal to "{c2:id}, {c3:id}"
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |

    When I go to "/new-ticket"
    Then the "ticket[ticket_field_{t}][data][]" multiple field should contain "{c2},{c3}"

  Scenario: I check date field's default value
    Given only the following custom ticket fields exist:
      | # | Type | Title      | Default Value | Options                  |
      | t | date | Date field | 2016-06-15    | {"default_mode": "date"} |
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |

    When I go to "/new-ticket"
    Then the "ticket[ticket_field_{t}][data][year]" multiple field should contain 2016
    And the "ticket[ticket_field_{t}][data][month]" multiple field should contain 6
    And the "ticket[ticket_field_{t}][data][day]" multiple field should contain 15

  Scenario: I check datetime field's default value
    Given only the following custom ticket fields exist:
      | # | Type     | Title      | Default Value            | Options                  |
      | t | datetime | Date field | raw: 2016-06-15 20:30:00 | {"default_mode": "date"} |
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |

    When I go to "/new-ticket"
    Then the "ticket[ticket_field_{t}][data][date][year]" multiple field should contain 2016
    And the "ticket[ticket_field_{t}][data][date][month]" multiple field should contain 6
    And the "ticket[ticket_field_{t}][data][date][day]" multiple field should contain 15
    And the "ticket[ticket_field_{t}][data][time][hour]" multiple field should contain 20
    And the "ticket[ticket_field_{t}][data][time][minute]" multiple field should contain 30
