@new
Feature: /ticket_layouts endpoint
  I want to check layout fields criteria

  Background:
    Given I'm authenticated as admin
    And I have only default brand
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |

  Scenario: I check field w/o criteria
    Given the only default ticket layout exists with fields:
      | user_layout | user_layout_options |
      | department  | {"criteria": null}  |

    When I send a GET request to "/api/v2/ticket_layouts/user/default"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.fields[0].field_id" should be equal to the string "department"
    And the JSON node "data.fields[0].options.criteria" should be null

  Scenario: I check field with criteria
    Given the only default ticket layout exists with fields:
      | user_layout | user_layout_options                                                                                                          |
      | department  | {"criteria":{"version":1,"mode":"all","terms":[{"type":"CheckDepartment","op":"is","options":{"department_ids":["~d1~"]}}]}} |

    When I send a GET request to "/api/v2/ticket_layouts/user/default"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.fields[0].field_id" should be equal to the string "department"
    And the JSON node "data.fields[0].options.criteria.mode" should be equal to "all"
    And the JSON node "data.fields[0].options.criteria.terms" should have 1 element
    And the JSON node "data.fields[0].options.criteria.terms[0].type" should be equal to the string "CheckDepartment"
    And the JSON node "data.fields[0].options.criteria.terms[0].op" should be equal to the string "is"
    And the JSON node "data.fields[0].options.criteria.terms[0].options.department_ids" should have 1 element
    And the JSON node "data.fields[0].options.criteria.terms[0].options.department_ids[0]" should be equal to "{d1}"
