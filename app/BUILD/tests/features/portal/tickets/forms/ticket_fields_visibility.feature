@new
Feature: New ticket form
  I want to check fields visibility

  Background:
    Given I have only default brand
    And I'm authenticated as user
    And user has defaultBrand brand
    And I disable anti-abuse rate limiting
    And only the following Department records exist:
      | #  | Title        | Is Tickets Enabled |
      | d1 | Department 1 | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup registered

  Scenario: I check all fields are visible
    Given the only default ticket layout exists with fields:
      | user_layout |
      | department  |
      | cc          |
    When I go to "/new-ticket"
    Then I should see ".form-ticket" form fields in following order:
      | name               |
      | ticket[department] |
      | ticket[cc]         |
      | ticket[subject]    |

  Scenario: I check that some fields are hidden for the new ticket form
    Given the only default ticket layout exists with fields:
      | user_layout | user_layout_options     |
      | department  |                         |
      | cc          | {"on_newticket": false} |
    When I go to "/new-ticket"
    Then I should see ".form-ticket" form fields in following order:
      | name               |
      | ticket[department] |
      | ticket[subject]    |

  Scenario: I check that some fields are hidden for the new ticket form but presents on the edit form
    Given the only default ticket layout exists with fields:
      | user_layout | user_layout_options                            |
      | department  |                                                |
      | cc          | {"on_newticket": false, "on_editticket": true} |
    And only the following Ticket records exist:
      | #        | Person | Ref | Subject        | Date Last User Reply |
      | ticket_1 | {user} | ref | Ticket Subject | 2015-06-15 17:00:00  |
    And only the following TicketMessage records exist:
      | Ticket     | Person | Message |
      | {ticket_1} | {user} | text    |

    When I go to "/new-ticket"
    Then I should see ".form-ticket" form fields in following order:
      | name               |
      | ticket[department] |
      | ticket[subject]    |

    When I go to "/tickets/ref/edit"
    Then I should see ".form-ticket" form fields in following order:
      | name               |
      | ticket[department] |
      | ticket[cc]         |
      | ticket[subject]    |

  Scenario: I check that some fields are present on the new ticket form but hidden on the edit form
    Given the only default ticket layout exists with fields:
      | user_layout | user_layout_options                            |
      | department  |                                                |
      | cc          | {"on_newticket": true, "on_editticket": false} |

    When I go to "/new-ticket"
    Then I should see ".form-ticket" form fields in following order:
      | name               |
      | ticket[department] |
      | ticket[cc]         |
      | ticket[subject]    |

    When I go to "/tickets/ref/edit"
    Then I should see ".form-ticket" form fields in following order:
      | name               |
      | ticket[department] |
      | ticket[subject]    |

  Scenario: I check visible field are present on the widget new ticket form
    Given the only default ticket layout exists with fields:
      | user_layout | user_layout_options     |
      | department  |                         |
      | cc          | {"on_newticket": true} |

    When I go to "/portal/api/tickets/new"
    Then the response should contain "<input type=\\"text\\" id=\\"ticket_cc\\" name=\\"ticket[cc]\\""

  Scenario: I check hidden fields on the widget new ticket form
    Given the only default ticket layout exists with fields:
      | user_layout | user_layout_options     |
      | department  |                         |
      | cc          | {"on_newticket": false} |

    When I go to "/portal/api/tickets/new"
    Then the response should not contain "<input type=\\"text\\" id=\\"ticket_cc\\" name=\\"ticket[cc]\\""

  Scenario: I check that department field is on the form even it's not visible
    Given the only default ticket layout exists with fields:
      | user_layout | user_layout_options     |
      | department  | {"on_newticket": false} |
      | subject     | {"on_newticket": false} |
      | message     | {"on_newticket": false} |
      | person      | {"on_newticket": false} |

    When I go to "/new-ticket"
    Then I should see ".form-ticket" form fields in following order:
      | name                                 |
      | ticket[department]                   |
      | ticket[subject]                      |
      | ticket[message][message]             |
      | ticket[message][format]              |
      | ticket[attachments][0][blob][upload] |
      | ticket[person][user_name]            |
