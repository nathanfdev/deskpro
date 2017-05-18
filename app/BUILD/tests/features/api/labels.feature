@new
Feature: /*_labels endpoints
  To retrieve labels of different DeskPRO objects
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as admin
    And no LabelDef records exist

  Scenario Outline: I get labels
    Given only the following LabelDef records exist:
      | Label          | Color   | Label Type |
      | label_<type>_1 | #FFFFFF | <type>     |
      | label_<type>_2 | #FFFFFF | <type>     |
      | label_<type>_3 | #FFFFFF | <type>     |

    When I send a GET request to "/api/v2/<target>_labels"
    Then the JSON node "data[0].label" should be equal to "label_<type>_1"
    And the JSON node "data[0].color" should exist

    Examples:
      | target       | type          |
      | person       | people        |
      | organization | organizations |
      | ticket       | tickets       |
      | task         | task          |

  Scenario Outline: I search for labels
    Given only the following LabelDef records exist:
      | Label          | Color   | Label Type |
      | label_<type>_1 | #FFFFFF | <type>     |
      | label_<type>_2 | #FFFFFF | <type>     |
      | label_<type>_3 | #FFFFFF | <type>     |

    When I send a GET request to "/api/v2/<target>_labels?term=<type>_2"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].label" should be equal to "label_<type>_2"

    Examples:
      | target       | type          |
      | person       | people        |
      | organization | organizations |
      | ticket       | tickets       |
      | task         | task          |

  Scenario: Labels sideloading
    Given only the following User records exist:
      | #  | Name   |
      | p1 | User 1 |
      | p2 | User 2 |
      | p3 | User 3 |
    And only the following LabelPerson records exist:
      | Person | Label          |
      | {p1}   | person_label_1 |
      | {p1}   | person_label_2 |
      | {p2}   | person_label_3 |
    And only the following Ticket records exist:
      | #  | Subject  | Person |
      | t1 | Ticket 1 | {p1}   |
      | t2 | Ticket 2 | {p2}   |
    And only the following LabelTicket records exist:
      | Ticket | Label          |
      | {t1}   | ticket_label_1 |
      | {t1}   | ticket_label_2 |
      | {t2}   | ticket_label_3 |

    When I send a GET request to "/api/v2/tickets?include=person,label_person,label_ticket"
    Then the JSON node "linked.person.{p1}" should exist
    And the JSON node "linked.person.{p2}" should exist
    And the JSON node "linked.label_person.person_label_1" should exist
    And the JSON node "linked.label_person.person_label_2" should exist
    And the JSON node "linked.label_person.person_label_3" should exist
    And the JSON node "linked.label_ticket.ticket_label_1" should exist
    And the JSON node "linked.label_ticket.ticket_label_2" should exist
    And the JSON node "linked.label_ticket.ticket_label_3" should exist
