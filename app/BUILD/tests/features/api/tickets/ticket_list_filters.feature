Feature: /tickets endpoint
  Check list filters

  Background:
    Given no Ticket records exist
    And I'm authenticated as admin

  Scenario Outline: I filter by 'label'
    Given the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |
      | t2 | Ticket 2 |
      | t3 | Ticket 3 |
    And only the following LabelTicket records exist:
      | Ticket | Label  |
      | {t1}   | label1 |
      | {t1}   | label2 |
      | {t2}   | label2 |
      | {t3}   | label3 |

    When I send a GET request to "/api/v2/tickets?<filter_name>=label2&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{t1}"
    And the JSON node "data[1].id" should be equal to "{t2}"

    When I send a GET request to "/api/v2/tickets?<filter_name>[]=label1&<filter_name>[]=label2&<filter_name>[]=label3&order_dir=asc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{t1}"
    And the JSON node "data[1].id" should be equal to "{t2}"
    And the JSON node "data[2].id" should be equal to "{t3}"

    Examples:
      | filter_name |
      | label       |
      | labels      |

  Scenario Outline: I filter by 'status', 'urgency'
    Given the following Ticket records exist:
      | #  | Subject  | <prop_name>    |
      | t1 | Ticket 1 | <filter_val_1> |
      | t2 | Ticket 2 | <filter_val_1> |
      | t3 | Ticket 3 | <filter_val_2>  |

    When I send a GET request to "/api/v2/tickets?<filter_name>=<filter_val_1>&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{t1}"
    And the JSON node "data[1].id" should be equal to "{t2}"

    When I send a GET request to "/api/v2/tickets?<filter_name>[]=<filter_val_1>&<filter_name>[]=<filter_val_2>&order_dir=asc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{t1}"
    And the JSON node "data[1].id" should be equal to "{t2}"
    And the JSON node "data[2].id" should be equal to "{t3}"

    Examples:
      | prop_name | filter_name | filter_val_1   | filter_val_2  |
      | Status    | status      | awaiting_agent | awaiting_user |
      | Urgency   | urgency     | 8              | 10            |

  Scenario: I filter by 'not-status'
    Given the following Ticket records exist:
      | #  | Subject  | Status         |
      | t1 | Ticket 1 | awaiting_agent |
      | t2 | Ticket 2 | awaiting_agent |
      | t3 | Ticket 3 | awaiting_user  |

    When I send a GET request to "/api/v2/tickets?not_status=awaiting_user&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{t1}"
    And the JSON node "data[1].id" should be equal to "{t2}"

    When I send a GET request to "/api/v2/tickets?not_status[]=archived&not_status[]=resolved&order_dir=asc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{t1}"
    And the JSON node "data[1].id" should be equal to "{t2}"
    And the JSON node "data[2].id" should be equal to "{t3}"

  Scenario: I filter by 'agent'
    Given "agent1@deskpro.dev" agent exists
    And "agent2@deskpro.dev" agent exists
    And the following Ticket records exist:
      | #  | Subject  | Agent                |
      | t1 | Ticket 1 | {agent1@deskpro.dev} |
      | t2 | Ticket 2 | {agent1@deskpro.dev} |
      | t3 | Ticket 3 | {agent2@deskpro.dev} |

    When I send a GET request to "/api/v2/tickets?agent={agent1@deskpro.dev}&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{t1}"
    And the JSON node "data[1].id" should be equal to "{t2}"

    When I send a GET request to "/api/v2/tickets?agent[]={agent1@deskpro.dev}&agent[]={agent2@deskpro.dev}&order_dir=asc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{t1}"
    And the JSON node "data[1].id" should be equal to "{t2}"
    And the JSON node "data[2].id" should be equal to "{t3}"

  Scenario Outline: I filter by 'person' and 'email'
    Given "user1@deskpro.dev" user exists
    And "user2@deskpro.dev" user exists
    And the following Ticket records exist:
      | #  | Subject  | Person              |
      | t1 | Ticket 1 | {user1@deskpro.dev} |
      | t2 | Ticket 2 | {user1@deskpro.dev} |
      | t3 | Ticket 3 | {user2@deskpro.dev} |

    When I send a GET request to "/api/v2/tickets?<filter_name>=<filter_val_1>&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{t1}"
    And the JSON node "data[1].id" should be equal to "{t2}"

    When I send a GET request to "/api/v2/tickets?<filter_name>[]=<filter_val_1>&<filter_name>[]=<filter_val_2>&order_dir=asc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{t1}"
    And the JSON node "data[1].id" should be equal to "{t2}"
    And the JSON node "data[2].id" should be equal to "{t3}"

  Examples:
    | filter_name | filter_val_1        | filter_val_2        |
    | person      | {user1@deskpro.dev} | {user2@deskpro.dev} |
    | email       | user1@deskpro.dev   | user2@deskpro.dev   |

  Scenario Outline: I filter by 'organization', 'language', 'department', 'agent_team', 'sla'
    Given only the following <entity_name> records exist:
      | #  | <title_prop> |
      | o1 | Obj 1        |
      | o2 | Obj 2        |
      | o3 | Obj 3        |
    And the following Ticket records exist:
      | #  | Subject  | <entity_name> |
      | t1 | Ticket 1 | {o1}          |
      | t2 | Ticket 2 | {o2}          |
      | t3 | Ticket 3 | {o2}          |

    When I send a GET request to "/api/v2/tickets?<filter_name>={o2}&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{t2}"
    And the JSON node "data[1].id" should be equal to "{t3}"

    When I send a GET request to "/api/v2/tickets?<filter_name>[]={o1}&<filter_name>[]={o2}&order_dir=asc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{t1}"
    And the JSON node "data[1].id" should be equal to "{t2}"
    And the JSON node "data[2].id" should be equal to "{t3}"

    Examples:
      | entity_name  | filter_name  | title_prop |
      | Organization | organization | Name       |
      | Language     | language     | Title      |
      | Department   | department   | Title      |
      | AgentTeam    | agent_team   | Name       |
      | Sla          | sla          | Title      |

  Scenario: I filter by 'problem'
    Given only the following Problem records exist:
      | #  | Title     |
      | p1 | Problem 1 |
      | p2 | Problem 2 |
      | p3 | Problem 3 |
    And the following Ticket records exist:
      | #  | Subject  | Problems |
      | t1 | Ticket 1 | [{p1}]   |
      | t2 | Ticket 2 | [{p2}]   |
      | t3 | Ticket 3 | [{p2}]   |

    When I send a GET request to "/api/v2/tickets?problem={p2}&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{t2}"
    And the JSON node "data[1].id" should be equal to "{t3}"

    When I send a GET request to "/api/v2/tickets?problem[]={p1}&problem[]={p2}&order_dir=asc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{t1}"
    And the JSON node "data[1].id" should be equal to "{t2}"
    And the JSON node "data[2].id" should be equal to "{t3}"

  Scenario: I filter by 'sla_status'
    Given only the following Sla records exist:
      | #  | Title |
      | p1 | Sla 1 |
      | p2 | Sla 2 |
      | p3 | Sla 3 |
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |
      | t2 | Ticket 2 |
      | t3 | Ticket 3 |
    And only the following TicketSla records exist:
      | #   | Sla  | Ticket | Sla Status |
      | ts1 | {p1} | {t1}   | warning    |
      | ts2 | {p1} | {t2}   | ok         |
      | ts3 | {p1} | {t3}   | ok         |

    When I send a GET request to "/api/v2/tickets?sla_status=ok&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{t2}"
    And the JSON node "data[1].id" should be equal to "{t3}"

    When I send a GET request to "/api/v2/tickets?sla_status[]=warning&sla_status[]=ok&order_dir=asc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{t1}"
    And the JSON node "data[1].id" should be equal to "{t2}"
    And the JSON node "data[2].id" should be equal to "{t3}"

  Scenario Outline: I filter by 'star'
    Given only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |
      | t2 | Ticket 2 |
      | t3 | Ticket 3 |
    And only the following TicketFlagged records exist:
      | Person  | Ticket | color |
      | {admin} | {t1}   | green |
      | {admin} | {t2}   | blue  |
      | {admin} | {t3}   | blue  |

    When I send a GET request to "/api/v2/tickets?star=<filter_val_1>&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{t2}"
    And the JSON node "data[1].id" should be equal to "{t3}"

    When I send a GET request to "/api/v2/tickets?star[]=<filter_val_1>&star[]=<filter_val_2>&order_dir=asc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{t1}"
    And the JSON node "data[1].id" should be equal to "{t2}"
    And the JSON node "data[2].id" should be equal to "{t3}"

    Examples:
      | filter_val_1 | filter_val_2 |
      | blue         | green        |
      | 1            | 2            |

  Scenario: I filter by text 'custom field'
    Given only the following custom ticket fields exist:
      | #  | Type | Title      |
      | f1 | text | Text field |
      | f2 | text | Text field |
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |
      | t2 | Ticket 2 |
      | t3 | Ticket 3 |
    And the object "t1" has "f1" custom data set to "text1"
    And the object "t2" has "f1" custom data set to "text2"
    And the object "t3" has "f1" custom data set to "some data"
    And the object "t3" has "f2" custom data set to "text"

    When I send a GET request to "/api/v2/tickets?ticket_field.{f1}=text&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{t1}"
    And the JSON node "data[1].id" should be equal to "{t2}"

    When I send a GET request to "/api/v2/tickets?ticket_field.{f1}=some&order_dir=asc"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{t3}"


  Scenario: I filter by date 'custom field'
    Given only the following custom ticket fields exist:
      | #  | Type | Title      |
      | f1 | date | Date field |
      | f2 | date | Date field |
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |
      | t2 | Ticket 2 |
      | t3 | Ticket 3 |
    And the object "t1" has "f1" custom data set to "2017-04-20"
    And the object "t2" has "f1" custom data set to "2017-04-18"

    When I send a GET request to "/api/v2/tickets?ticket_field.{f1}[from]=2017-04-15&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{t1}"
    And the JSON node "data[1].id" should be equal to "{t2}"

    When I send a GET request to "/api/v2/tickets?ticket_field.{f1}[from]=2017-04-19&order_dir=asc"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{t1}"

    When I send a GET request to "/api/v2/tickets?ticket_field.{f1}[to]=2017-04-19&order_dir=asc"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{t2}"

  Scenario: I filter by choice 'custom field'
    Given only the following custom ticket fields exist:
      | #  | Type          | Title        | Parent |
      | f1 | single_choice | Choice field |        |
      | c1 |               | Choice 1     | {f1}   |
      | c2 |               | Choice 2     | {f1}   |
      | c3 |               | Choice 3     | {f1}   |
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |
      | t2 | Ticket 2 |
      | t3 | Ticket 3 |
    And the object "t1" has "f1" custom data set to "{c1}"
    And the object "t2" has "f1" custom data set to "{c2}"

    When I send a GET request to "/api/v2/tickets?ticket_field.{f1}={c1}&order_dir=asc"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{t1}"

    When I send a GET request to "/api/v2/tickets?ticket_field.{f1}={c2}&order_dir=asc"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{t2}"

    When I send a GET request to "/api/v2/tickets?ticket_field.{f1}[]={c1}&ticket_field.{f1}[]={c2}&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{t1}"
    And the JSON node "data[1].id" should be equal to "{t2}"
