@new
Feature: /mass_actions/tickets endpoint
  To complete mass actions on tickets list
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as admin
    And  I add "admin" usergroup relation "agent_all_perms"
    And I add "admin" usergroup relation "agent_all_safe_perms"
    And agent and user exist
    And I have only default brand
    And only the following TicketCategory records exist:
      | #  | Title      |
      | c1 | Category 1 |
      | c2 | Category 2 |
    And only the following AgentTeam records exist:
      | #   | Name   |
      | at1 | Team 1 |
      | at2 | Team 2 |
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
      | d2 | Department 2 | [{defaultBrand}] | 1                  |
    And only the following Ticket records exist:
      | #  | Subject   | Status       |
      | t1 | Ticket 1 | awaiting_user |
      | t2 | Ticket 2 | awaiting_user |

  Scenario: I change status
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [~t1~, ~t2~],
  "params":{"set_status": "awaiting_agent"}
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].status" should be equal to "awaiting_agent"
    And the JSON node "data[1].status" should be equal to "awaiting_agent"

  Scenario: I set incorrect status
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [~t1~],
  "params":{"set_status":1}
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.params.fields.set_status.errors[0].code" should be equal to "bad_choice"

  Scenario Outline: I set an object
    And only the following <entity_type> records exist:
      | #  | Title    |
      | o1 | Object 1 |
      | o2 | Object 2 |

    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [~t1~],
  "params":{"set_<prop>":~o2~}
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    And the JSON node "data.<prop>" should be equal to "{o2}"

    Examples:
      | prop     | entity_type    |
      | language | Language       |
      | product  | Product        |
      | category | TicketCategory |
      | workflow | TicketWorkflow |
      | priority | TicketPriority |

  Scenario Outline: I try to set a non-existing object
    Given no <entity_type> records exist
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [~t1~],
  "params": {"set_<prop>": 1}
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.params.fields.set_<prop>.errors[0].code" should be equal to "bad_choice"

    Examples:
      | prop     | entity_type    |
      | language | Language       |
      | product  | Product        |
      | category | TicketCategory |
      | workflow | TicketWorkflow |
      | priority | TicketPriority |

  Scenario Outline: I assign an object
    And only the following <entity_type> records exist:
      | #  | <title>  |
      | o1 | Object 1 |
      | o2 | Object 2 |

    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [~t1~],
  "params":{"assign": {"<set_prop>": ~o2~}}
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    And the JSON node "data.<ticket_prop>" should be equal to "{o2}"

    Examples:
      | set_prop   | ticket_prop | entity_type | title |
      | agent      | agent       | Agent       | Name  |
      | team       | agent_team  | AgentTeam   | Name  |
      | department | department  | Department  | Title |

  Scenario Outline: I try to assign a non-existing object
    Given no <entity_type> records exist
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [~t1~],
  "params": {"assign":{"<prop>":1}}
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.params.fields.assign.fields.<prop>.errors[0].code" should be equal to "bad_choice"

    Examples:
      | prop       | entity_type    |
      | team       | AgentTeam      |
      | department | Department     |

  Scenario: I delete tickets
    Given only the following TicketStatus records exist:
      | #   | StatusType     | SysId        | Title    |
      | ts1 | hidden         | deleted      | Deleted  |
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [~t1~,~t2~],
  "params":{
     "set_of_actions": ["delete"]
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets?status=hidden"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].status" should be equal to "hidden.{ts1}"
    And the JSON node "data[1].status" should be equal to "hidden.{ts1}"

  Scenario: I try to delete tickets w/o permissions
    Given  I remove "admin" usergroup relation "agent_all_perms"
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [~t1~,~t2~],
  "params":{
     "set_of_actions": ["delete"]
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.ids.errors[0].code" should be equal to "no_delete_permission"

  Scenario: I unassign participants
    Given only the following TicketParticipant records exist:
      | #  | Ticket | Person  |
      | p1 | {t1}   | {admin} |
      | p2 | {t2}   | {admin} |

    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [~t1~],
  "params":{
     "set_followers": []
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200
    And the JSON node "data.cc" should have 0 element

  Scenario: I apply set of actions
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [~t1~],
  "params":{
     "set_category": ~c2~,
     "assign": {
        "agent": ~admin~,
        "team": ~at1~,
        "department": ~d2~
     }
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200
    And the JSON node "data.category" should be equal to "{c2}"
    And the JSON node "data.agent" should be equal to "{admin}"
    And the JSON node "data.agent_team" should be equal to "{at1}"
    And the JSON node "data.department" should be equal to "{d2}"

  Scenario: I apply unassign action on ticket
    Given the following Ticket records exist:
      | #  | Subject   | Status        | Agent   | AgentTeam | Department |
      | t3 | Ticket 3  | awaiting_user | {admin} | {at1}     | {d2}       |

    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [~t3~],
  "params":{
     "assign": {
        "agent":      null,
        "team":       null,
        "department": null
     }
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t3}"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data.agent" should be null
    And the JSON node "data.agent_team" should be null
    And the JSON node "data.department" should be equal to "{d1}"
