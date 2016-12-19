@new
Feature: /task_projects/{id}/members/(agents|departments|teams) endpoint
  To CRUD DeskPRO person project_members
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "admin"
    And only the following "TaskProject" records exist:
      | #           | title        |
      | testProject | Test project |
    And "ta1@deskpro.dev" agent exists
    And "ta2@deskpro.dev" agent exists
    And the following "Department" records exist:
      | #           | title       | is_tickets_enabled | is_chat_enabled |
      | department1 | Department1 | 1                  | 1               |
      | department2 | Department2 | 1                  | 1               |
    And the following "AgentTeam" records exist:
      | #          | name        |
      | agentTeam1 | Agent Team1 |
      | agentTeam2 | Agent Team2 |
  Scenario Outline: I try to create member with malformed request
    When I send a POST request to "/api/v2/task_projects/{testProject}/members/<type>"
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "required"
    And the JSON node "errors.errors[0].message" should contain "This value should not be blank."

    Examples:
      | type        |
      | agents      |
      | departments |
      | teams       |

  Scenario Outline: I check validation
    When I send a POST request to "/api/v2/task_projects/{testProject}/members/<type>" with body:
    """
{
  "member": <ref>,
  "unknown": 1
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.errors[0].message" should be equal to "Unexpected field names: unknown"

    Examples:
      | type        | ref          |
      | agents      | ~me~         |
      | departments | ~department1~ |
      | teams       | ~agentTeam1~  |

  Scenario Outline: I create a person member
    When I send a POST request to "/api/v2/task_projects/{testProject}/members/<type>" with body:
    """
{
  "member": <ref1>
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to "<ref1>"
    And the JSON node "data.<param>" should be equal to "<value1>"

    When I send a POST request to "/api/v2/task_projects/{testProject}/members/<type>" with body:
    """
{
  "member": <ref2>
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to "<ref2>"
    And the JSON node "data.<param>" should be equal to "<value2>"

    Examples:
      | type        | param         | value1          | value2          | ref1              | ref2              |
      | agents      | primary_email | ta1@deskpro.dev | ta2@deskpro.dev | ~ta1@deskpro.dev~ | ~ta2@deskpro.dev~ |
      | departments | title         | Department1     | Department2     | ~department1~     | ~department2~     |
      | teams       | name          | Agent Team1     | Agent Team2     | ~agentTeam1~      | ~agentTeam2~      |

  Scenario Outline: I try to create member with the same id
    Given only the following "ProjectMember" records exist:
      | project       | person            | team         | department    |
      | {testProject} | {ta1@deskpro.dev} |              |               |
      | {testProject} |                   | {agentTeam1} |               |
      | {testProject} |                   |              | {department1} |
    When I send a POST request to "/api/v2/task_projects/{testProject}/members/<type>" with body:
    """
{
  "member": <ref>
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.member.errors[0].code" should be equal to "unique_entity"
    And the JSON node "errors.fields.member.errors[0].message" should contain "This value already exists in the system."

    Examples:
      | type        | ref               |
      | agents      | ~ta1@deskpro.dev~ |
      | departments | ~department1~     |
      | teams       | ~agentTeam1~      |

  Scenario Outline: I retrieve a list of project members
    Given only the following "ProjectMember" records exist:
      | project       | person            | team         | department    |
      | {testProject} | {ta1@deskpro.dev} |              |               |
      | {testProject} | {ta2@deskpro.dev} |              |               |
      | {testProject} |                   | {agentTeam1} |               |
      | {testProject} |                   | {agentTeam2} |               |
      | {testProject} |                   |              | {department1} |
      | {testProject} |                   |              | {department2} |
    When I send a GET request to "/api/v2/task_projects/{testProject}/members/<type>"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements

    And the JSON node "data[0].id" should be equal to "<ref2>"
    And the JSON node "data[0].<param>" should be equal to "<value2>"

    And the JSON node "data[1].id" should be equal to "<ref1>"
    And the JSON node "data[1].<param>" should be equal to "<value1>"

    Examples:
      | type        | param         | value1          | value2          | ref1              | ref2              |
      | agents      | primary_email | ta1@deskpro.dev | ta2@deskpro.dev | ~ta1@deskpro.dev~ | ~ta2@deskpro.dev~ |
      | departments | title         | Department1     | Department2     | ~department1~     | ~department2~     |
      | teams       | name          | Agent Team1     | Agent Team2     | ~agentTeam1~      | ~agentTeam2~      |

  Scenario: I get single agent project member.
    Given only the following "ProjectMember" records exist:
      | project       | person            | team         | department    |
      | {testProject} | {ta1@deskpro.dev} |              |               |
    When I send a GET request to "/api/v2/task_projects/{testProject}/members/agents/{ta1@deskpro.dev}?include=usergroup"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to "{ta1@deskpro.dev}"
    And the JSON node "data.primary_email" should be equal to "ta1@deskpro.dev"
    And the JSON node "linked.usergroup" should have 1 element

  Scenario Outline: I get single department/team project member
    Given only the following "ProjectMember" records exist:
      | project       | person            | team         | department    |
      | {testProject} |                   | {agentTeam1} |               |
      | {testProject} |                   |              | {department1} |
    When I send a GET request to "/api/v2/task_projects/{testProject}/members/<type>/<ref>"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to "<ref>"
    And the JSON node "data.<param>" should be equal to "<value>"

    Examples:
      | type        | param | value       | ref           |
      | departments | title | Department1 | ~department1~ |
      | teams       | name  | Agent Team1 | ~agentTeam1~  |

  Scenario Outline: I get a person project member tasks
    Given only the following "ProjectMember" records exist:
      | project       | person            | team         | department    |
      | {testProject} | {ta1@deskpro.dev} |              |               |
      | {testProject} |                   | {agentTeam1} |               |
      | {testProject} |                   |              | {department1} |
    When I send a GET request to "/api/v2/task_projects/{testProject}/members/<type>/<ref>/tasks"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements

    Examples:
      | type        | ref               |
      | agents      | ~ta1@deskpro.dev~ |
      | departments | ~department1~     |
      | teams       | ~agentTeam1~       |

  Scenario Outline: I delete a project member
    Given only the following "ProjectMember" records exist:
      | project       | person            | team         | department    |
      | {testProject} | {ta1@deskpro.dev} |              |               |
      | {testProject} |                   | {agentTeam1} |               |
      | {testProject} |                   |              | {department1} |
    When I send a DELETE request to "/api/v2/task_projects/{testProject}/members/<type>/<ref>"
    Then the response status code should be 200

    Examples:
      | type        | ref |
      | agents      | ~ta1@deskpro.dev~ |
      | departments | ~department1~    |
      | teams       | ~agentTeam1~      |
