@new
Feature: ticket_filters2
  To CRUD ticket_filters2
  As an API user
  I want an API endpoint

  Background:
    And no TicketFilterSet records exist
    And no TicketFilterSetAssoc records exist
    And no TicketFilter records exist

  Scenario: Admin looking at full list of filters
    Given I'm authenticated as admin
    Given only the following TicketFilterSet records exist:
      | #  | Title    | Is Global | Display Order |
      | s1 | Inbox    | 1         | 10            |

    Given only the following TicketFilter records exist:
      | #  | Title              | Is Enabled | Query                                                                 |
      | f5 | Assigned To Me     | 1          | ticket.status = 'awaiting_agent' AND ticket.agent = $me               |
      | f4 | Tickets I Follow   | 1          | ticket.status = 'awaiting_agent' AND ticket.followers HAS $me         |
      | f3 | Assigned To Team   | 1          | ticket.status = 'awaiting_agent' AND ticket.agent_team IN $my_teams   |
      | f2 | Unassigned         | 1          | ticket.status = 'awaiting_agent' AND ticket.agent IS EMPTY            |
      | f1 | All Awaiting Agent | 1          | ticket.status = 'awaiting_agent'                                      |

    Given only the following TicketFilterSetAssoc records exist:
      | Filter Set | Filter | Display Order |
      | {s1}       | {f1}   | 50            |
      | {s1}       | {f2}   | 40            |
      | {s1}       | {f3}   | 30            |
      | {s1}       | {f4}   | 20            |
      | {s1}       | {f5}   | 10            |

    When I send a GET request to "/api/v2/ticket_filters2"
      Then the response status code should be 200
      And the response should be in JSON

      And the JSON node "data" should have 5 elements
      And the JSON node "data[0].id" should be equal to "{f5}"
      And the JSON node "data[0].title" should be equal to "Assigned To Me"
      And the JSON node "data[0].query" should be equal to "ticket.status = 'awaiting_agent' AND ticket.agent = $me"
      And the JSON node "data[0].is_enabled" should be true
      And the JSON node "data[4].id" should be equal to "{f1}"

    When I send a GET request to "/api/v2/ticket_filters2_sets"
      Then the response status code should be 200
      And the response should be in JSON

      And the JSON node "data" should have 1 elements
      And the JSON node "data[0].id" should be equal to "{s1}"
      And the JSON node "data[0].title" should be equal to "Inbox"
      And the JSON node "data[0].display_order" should be equal to the number 10
      And the JSON node "data[0].is_global" should be true
      And the JSON node "data[0].shared_teams" should have 0 elements
      And the JSON node "data[0].shared_agents" should have 0 elements
      And the JSON node "data[0].filters" should be equal to node:
        """
        [{f5}, {f4}, {f3}, {f2}, {f1}]
        """

  Scenario: Looking list of own filters
    Given I'm authenticated as agent

    Given the following "AgentTeam" records exist:
      | #     | Name        | Members   |
      | team  | Some Team   | [{agent}] |

    Given only the following TicketFilterSet records exist:
      | #  | Title       | Is Global | Display Order | Shared Agents | Shared Teams |
      | s1 | Test Set A  | 0         | 10            | [{agent}]     | []           |
      | s2 | Test Set B  | 0         | 20            | []            | [{team}]     |
      | s3 | Test Set C  | 0         | 20            | [{admin}]     | []           |

    Given only the following TicketFilter records exist:
      | #  | Title      | Is Enabled | Query                            |
      | f1 | Filter A   | 1          | ticket.status = 'awaiting_agent' |
      | f2 | Filter B   | 1          | ticket.status = 'awaiting_user'  |
      | f3 | Filter C   | 0          | ticket.status = 'resolved'       |
      | f4 | Filter D   | 1          | ticket.status = 'hidden'         |

    Given only the following TicketFilterSetAssoc records exist:
      | Filter Set | Filter | Display Order |
      | {s1}       | {f1}   | 10            |
      | {s2}       | {f2}   | 20            |
      | {s2}       | {f3}   | 30            |
      | {s3}       | {f4}   | 40            |

    When I send a GET request to "/api/v2/ticket_filters2"
      Then the response status code should be 200
      And the response should be in JSON

      And the JSON node "data" should have 3 elements
      And the JSON node "data[0].id" should be equal to "{f1}"
      And the JSON node "data[1].id" should be equal to "{f2}"
      And the JSON node "data[2].id" should be equal to "{f3}"

    When I send a GET request to "/api/v2/ticket_filters2?enabled=1"
      Then the response status code should be 200
      And the response should be in JSON

      And the JSON node "data" should have 2 elements
      And the JSON node "data[0].id" should be equal to "{f1}"
      And the JSON node "data[1].id" should be equal to "{f2}"

    When I send a GET request to "/api/v2/ticket_filters2_sets"
      Then the response status code should be 200
      And the response should be in JSON

      And the JSON node "data" should have 2 elements
      And the JSON node "data[0].id" should be equal to "{s1}"
      And the JSON node "data[0].title" should be equal to "Test Set A"
      And the JSON node "data[0].display_order" should be equal to the number 10
      And the JSON node "data[0].is_global" should be false
      And the JSON node "data[0].shared_teams" should have 0 elements
      And the JSON node "data[0].shared_agents" should have 1 elements
      And the JSON node "data[0].filters" should be equal to node:
          """
          [{f1}]
          """

      And the JSON node "data[1].id" should be equal to "{s2}"
      And the JSON node "data[1].title" should be equal to "Test Set B"
      And the JSON node "data[1].display_order" should be equal to the number 20
      And the JSON node "data[1].is_global" should be false
      And the JSON node "data[1].shared_teams" should have 1 elements
      And the JSON node "data[1].shared_agents" should have 0 elements
      And the JSON node "data[1].filters" should be equal to node:
          """
          [{f2}, {f3}]
          """

    When I'm authenticated as admin
      And I send a GET request to "/api/v2/ticket_filters2_sets"
      Then the response status code should be 200
      And the response should be in JSON
      And the JSON node "data" should have 3 elements

    When I'm authenticated as admin
      And I send a GET request to "/api/v2/ticket_filters2_sets?mine=1"
      Then the response status code should be 200
      And the response should be in JSON
      And the JSON node "data" should have 1 elements
      And the JSON node "data[0].id" should be equal to "{s3}"

    When I'm authenticated as admin
      And I send a GET request to "/api/v2/ticket_filters2?mine=1"
      Then the response status code should be 200
      And the response should be in JSON
      And the JSON node "data" should have 1 elements
      And the JSON node "data[0].id" should be equal to "{f4}"