@new
Feature: /people endpoint
  Check list filters

  Background:
    Given there are no "Person" records
    And I'm authenticated as agent

  Scenario: I filter by 'primary_email'
    Given the following User records exist:
      | #  | Name   | Email             |
      | u1 | User 1 | user1@example.com |
      | u2 | User 2 | user2@example.com |
      | u3 | User 3 | user3@example.com |

    When I send a GET request to "/api/v2/people?primary_email=user2@example.com"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{u2}"

  Scenario: I filter by 'emails'
    Given the following User records exist:
      | #  | Name   | Email             |
      | u1 | User 1 | user1@example.com |
      | u2 | User 2 | user2@example.com |
      | u3 | User 3 | user3@example.com |

    When I send a GET request to "/api/v2/people?emails[]=user2@example.com&emails[]=user3@example.com&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{u2}"
    And the JSON node "data[1].id" should be equal to "{u3}"

  Scenario: I filter by 'organization'
    Given only the following Organization records exist:
      | #  | Name  |
      | o1 | Org 1 |
      | o2 | Org 2 |
      | o3 | Org 3 |
    And the following User records exist:
      | #  | Name   | Organization |
      | u1 | User 1 | {o1}         |
      | u2 | User 2 | {o2}         |
      | u3 | User 3 | {o2}         |

    When I send a GET request to "/api/v2/people?organization={o2}&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{u2}"
    And the JSON node "data[1].id" should be equal to "{u3}"

  Scenario: I filter by 'is_agent'
    Given the following User records exist:
      | #  | Name   |
      | u1 | User 1 |
      | u2 | User 2 |
      | u3 | User 3 |

    When I send a GET request to "/api/v2/people?is_agent=0&order_dir=asc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{u1}"
    And the JSON node "data[1].id" should be equal to "{u2}"
    And the JSON node "data[2].id" should be equal to "{u3}"

    When I send a GET request to "/api/v2/people?is_agent=1&order_dir=asc"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{agent}"

  Scenario: I filter by 'is_deleted'
    Given the following User records exist:
      | #  | Name   | Is Deleted |
      | u1 | User 1 | 0          |
      | u2 | User 2 | 0          |
      | u3 | User 3 | 1          |

    When I send a GET request to "/api/v2/people?is_deleted=0&order_dir=asc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{agent}"
    And the JSON node "data[1].id" should be equal to "{u1}"
    And the JSON node "data[2].id" should be equal to "{u2}"

    When I send a GET request to "/api/v2/people?is_deleted=1&order_dir=asc"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{u3}"

  Scenario: I filter by 'not_me'
    Given the following User records exist:
      | #  | Name   |
      | u1 | User 1 |
      | u2 | User 2 |
      | u3 | User 3 |

    When I send a GET request to "/api/v2/people?not_me=1&order_dir=asc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{u1}"
    And the JSON node "data[1].id" should be equal to "{u2}"
    And the JSON node "data[2].id" should be equal to "{u3}"

  Scenario: I filter by 'agent_team'
    Given the following AgentTeam records exist:
      | #  | Name   |
      | t1 | Team 1 |
      | t2 | Team 2 |
      | t3 | Team 3 |
    And the following User records exist:
      | #  | Name   | Is Agent | Primary Team |
      | u1 | User 1 | 1        | NULL         |
      | u2 | User 2 | 1        | {t2}         |
      | u3 | User 3 | 1        | {t2}         |

    When I send a GET request to "/api/v2/people?agent_team={t2}&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{u2}"
    And the JSON node "data[1].id" should be equal to "{u3}"

    When I send a GET request to "/api/v2/people?agent_team=&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{agent}"
    And the JSON node "data[1].id" should be equal to "{u1}"

  Scenario: I filter by 'user_group'
    Given the following Usergroup records exist:
      | #   | Title   |
      | ug1 | Group 1 |
      | ug2 | Group 2 |
      | ug3 | Group 3 |
    And the following User records exist:
      | #  | Name   | Usergroups     |
      | u1 | User 1 | [{ug1}, {ug2}] |
      | u2 | User 2 | [{ug2}, {ug3}] |
      | u3 | User 3 | []             |

    When I send a GET request to "/api/v2/people?user_group[]={ug1}&user_group[]={ug3}&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{u1}"
    And the JSON node "data[1].id" should be equal to "{u2}"

  Scenario: I filter by 'label'
    Given the following User records exist:
      | #  | Name   |
      | u1 | User 1 |
      | u2 | User 2 |
      | u3 | User 3 |
    And only the following LabelPerson records exist:
      | Person | Label  |
      | {u1}   | label1 |
      | {u1}   | label2 |
      | {u2}   | label2 |

    When I send a GET request to "/api/v2/people?label[]=label2&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{u1}"
    And the JSON node "data[1].id" should be equal to "{u2}"
