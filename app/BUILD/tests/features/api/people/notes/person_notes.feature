@new
Feature: /people/{id}/notes endpoint
  To CRUD DeskPRO person notes
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as admin
    And no PersonNote records exist

  Scenario: I try to get notes of not existed person
    When I send a GET request to "/api/v2/people/0/notes"
    Then the response status code should be 404

  Scenario: I try to create a note with empty request
    When I send a POST request to "/api/v2/people/{admin}/notes"
    Then the response status code should be 400
    And the JSON node "errors.fields.note.errors[0].code" should be equal to "required"

  Scenario: I create a note
    When I send a POST request to "/api/v2/people/{admin}/notes" with body:
    """
{
  "note": "my note"
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should exist
    And the JSON node "data.note" should be equal to "my note"
    And the JSON node "data.agent" should be equal to "{admin}"
    And the JSON node "data.person" should be equal to "{admin}"

  Scenario: I retrieve list of person notes
    Given only the following PersonNote records exist:
      | #  | Person  | Agent   | Note    | Date Created        |
      | n1 | {admin} | {admin} | my note | 2017-05-16 10:15:00 |

    When I send a GET request to "/api/v2/people/{admin}/notes"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 element

    And the JSON node "data[0].id" should be equal to "{n1}"
    And the JSON node "data[0].note" should be equal to "my note"
    And the JSON node "data[0].agent" should be equal to "{admin}"
    And the JSON node "data[0].person" should be equal to "{admin}"
    And the JSON node "data[0].date_created" should be equal to "2017-05-16T10:15:00+0000"

  Scenario: I modify a note
    Given only the following PersonNote records exist:
      | #  | Person  | Agent   | Note    |
      | n1 | {admin} | {admin} | my note |

    When I send a PUT request to "/api/v2/people/{admin}/notes/{n1}" with body:
    """
{
  "note": "my note (edited)"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/people/{admin}/notes/{n1}"
    Then the response status code should be 200
    And the JSON node "data.note" should be equal to "my note (edited)"
    And the JSON node "data.agent" should be equal to "{admin}"
    And the JSON node "data.person" should be equal to "{admin}"

  Scenario: I delete a note
    Given only the following PersonNote records exist:
      | #  | Person  | Agent   | Note    |
      | n1 | {admin} | {admin} | my note |

    When I send a DELETE request to "/api/v2/people/{admin}/notes/{n1}"
    Then the response status code should be 200
