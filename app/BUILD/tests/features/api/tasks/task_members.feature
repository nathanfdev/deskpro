@tasks
Feature: /task_projects/{id}/members/(agents|departments|teams) endpoint
  To CRUD DeskPRO person project_members
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario Outline: I try to create member with malformed request
    When I send a POST request to "/api/v2/task_projects/1/members/<type>"
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "required"
    And the JSON node "errors.errors[0].message" should contain "This value should not be blank."

    Examples:
      | type        |
      | agents      |
      | departments |
      | teams       |

  Scenario Outline: I check validation
    When I send a POST request to "/api/v2/task_projects/1/members/<type>" with body:
    """
{
  "member": 1,
  "unknown": 1
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.errors[0].message" should be equal to "Unexpected field names: unknown"

    Examples:
      | type        |
      | agents      |
      | departments |
      | teams       |

  Scenario Outline: I create a person member
    When I send a POST request to "/api/v2/task_projects/1/members/<type>" with body:
    """
{
  "member": 1
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.<param>" should be equal to "<value1>"

    When I send a POST request to "/api/v2/task_projects/1/members/<type>" with body:
    """
{
  "member": 2
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 2
    And the JSON node "data.<param>" should be equal to "<value2>"

    Examples:
      | type        | param         | value1            | value2            |
      | agents      | primary_email | admin@deskpro.dev | agent@deskpro.dev |
      | departments | title         | sales             | support           |
      | teams       | name          | test team         | Support Managers  |

  Scenario Outline: I try to create a person member with the same id
    When I send a POST request to "/api/v2/task_projects/1/members/<type>" with body:
    """
{
  "member": 2
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.member.errors[0].code" should be equal to "unique_entity"
    And the JSON node "errors.fields.member.errors[0].message" should contain "This value already exists in the system."

    Examples:
      | type        |
      | agents      |
      | departments |
      | teams       |

  Scenario Outline: I retrieve a list of project members
    When I send a GET request to "/api/v2/task_projects/1/members/<type>"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements

    And the JSON node "data[0].id" should be equal to 2
    And the JSON node "data[0].<param>" should be equal to "<value2>"

    And the JSON node "data[1].id" should be equal to 1
    And the JSON node "data[1].<param>" should be equal to "<value1>"

    Examples:
      | type        | param         | value1            | value2            |
      | agents      | primary_email | admin@deskpro.dev | agent@deskpro.dev |
      | departments | title         | sales             | support           |
      | teams       | name          | test team         | Support Managers  |

  Scenario: I get single agent project member
    When I send a GET request to "/api/v2/task_projects/1/members/agents/2?include=usergroup"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 2
    And the JSON node "data.primary_email" should be equal to "agent@deskpro.dev"
    And the JSON node "linked.usergroup.1.title" should be equal to "Everyone"

  Scenario Outline: I get single department/team project member
    When I send a GET request to "/api/v2/task_projects/1/members/<type>/2"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 2
    And the JSON node "data.<param>" should be equal to "<value>"

    Examples:
      | type        | param | value             |
      | departments | title | support           |
      | teams       | name  | Support Managers  |

  Scenario Outline: I get a person project member tasks
    When I send a GET request to "/api/v2/task_projects/1/members/<type>/1/tasks"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements

    Examples:
      | type        |
      | agents      |
      | departments |
      | teams       |

  Scenario Outline: I delete a project member
    When I send a DELETE request to "/api/v2/task_projects/1/members/<type>/1"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/task_projects/1/members/<type>/1"
    Then the response status code should be 404

    Examples:
      | type        |
      | agents      |
      | departments |
      | teams       |
