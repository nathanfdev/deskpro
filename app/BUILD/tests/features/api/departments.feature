@new
Feature: /(ticket|chat)_departments endpoint
  To CRUD DeskPRO ticket departments
  As a developer
  I want an API endpoint

  Background:
    Given I'm authenticated as admin
    And I have default brand
    And no Department records exist

  Scenario: I retrieve a list of departments
    Given only the following Department records exist:
      | #  | Title               | Is Tickets Enabled | Is Chat Enabled |
      | d1 | Ticket Department 1 | 1                  | 0               |
      | d2 | Ticket Department 2 | 1                  | 0               |
      | d3 | Chat Department 1   | 0                  | 1               |
      | d4 | Chat Department 2   | 0                  | 1               |

    When I send a GET request to "/api/v2/ticket_departments"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].title" should be equal to the string "Ticket Department 1"
    And the JSON node "data[1].title" should be equal to the string "Ticket Department 2"

    When I send a GET request to "/api/v2/chat_departments"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].title" should be equal to the string "Chat Department 1"
    And the JSON node "data[1].title" should be equal to the string "Chat Department 2"

  Scenario Outline: I check department props
    Given only the following Department records exist:
      | #  | Title        | Parent | Brands           | Is Tickets Enabled | Is Chat Enabled |
      | d1 | Department 1 | NULL   | [{defaultBrand}] | 1                  | 1               |
      | d2 | Department 2 | {d1}   | [{defaultBrand}] | 1                  | 1               |

    When I send a GET request to "/api/v2/<type>_departments/{d2}"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to "{d2}"
    And the JSON node "data.parent" should be equal to "{d1}"
    And the JSON node "data.title" should be equal to "Department 2"
    And the JSON node "data.user_title" should be equal to "Department 2"
    And the JSON node "data.display_order" should exist
    And the JSON node "data.brands" should have 1 element
    And the JSON node "data.brands[0]" should be equal to "{defaultBrand}"

    Examples:
      | type   |
      | ticket |
      | chat   |

  Scenario Outline: I try to get wrong department type
    Given only the following Department records exist:
      | #  | Title        | Is Tickets Enabled   | Is Chat Enabled   |
      | d1 | Department 1 | <is_tickets_enabled> | <is_chat_enabled> |

    When I send a GET request to "/api/v2/<type>_departments/{d1}"
    Then the response status code should be 404

    Examples:
      | type   | is_tickets_enabled | is_chat_enabled |
      | ticket | 0                  | 1               |
      | chat   | 1                  | 0               |

  Scenario Outline: I get department agents
    Given only the following Department records exist:
      | #  | Title        | Is Tickets Enabled | Is Chat Enabled |
      | d1 | Department 1 | 1                  | 1               |

    When I send a GET request to "/api/v2/<type>_departments/{d1}/agents"
    Then the response status code should be 200

    Examples:
      | type   |
      | ticket |
      | chat   |

  Scenario Outline: I retrieve a list of ticket departments by ids
    Given only the following Department records exist:
      | #  | Title        | Is Tickets Enabled | Is Chat Enabled |
      | d1 | Department 1 | 1                  | 1               |
      | d2 | Department 2 | 1                  | 1               |
      | d3 | Department 3 | 1                  | 1               |

    When I send a GET request to "/api/v2/<type>_departments?ids[0]="
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements

    When I send a GET request to "/api/v2/<type>_departments?ids[0]={d1}&ids[1]={d3}"
    Then the response status code should be 200
    And the JSON node "data" should have 2 element
    And the JSON node "data[0].title" should be equal to the string "Department 1"
    And the JSON node "data[1].title" should be equal to the string "Department 3"

    Examples:
      | type   |
      | ticket |
      | chat   |

  Scenario Outline: I try to create a new ticket department with empty request
    When I send a POST request to "/api/v2/<type>_departments"
    Then the response status code should be 400
    And the JSON node "errors.fields.title.errors[0].code" should be equal to "required"

    Examples:
      | type   |
      | ticket |
      | chat   |

  Scenario Outline: I create a new department with default brand
    When I send a POST request to "/api/v2/<type>_departments" with body:
    """
{
  "title": "new department"
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should exist
    And the JSON node "data.title" should be equal to "new department"
    And the JSON node "data.user_title" should be equal to "new department"
    And the JSON node "data.display_order" should be equal to 0
    And the JSON node "data.brands" should have 1 element
    And the JSON node "data.brands[0]" should be equal to "{defaultBrand}"

    When I send a GET request to "/api/v2/<type>_departments/{lastCreatedId}"
    Then the response status code should be 200

    Examples:
      | type   |
      | ticket |
      | chat   |

  Scenario Outline: I create department with brands
    Given only the following Brand records exist:
      | #  | Name    |
      | b1 | Brand 1 |
      | b2 | Brand 2 |
      | b3 | Brand 3 |

    When I send a POST request to "/api/v2/<type>_departments" with body:
    """
{
  "title": "new department",
  "brands": [~b1~, ~b3~]
}
    """
    Then the response status code should be 201
    And the JSON node "data.brands" should have 2 elements
    And the JSON node "data.brands[0]" should be equal to "{b1}"
    And the JSON node "data.brands[1]" should be equal to "{b3}"

    Examples:
      | type   |
      | ticket |
      | chat   |

  Scenario Outline: I edit department
    Given only the following Department records exist:
      | #  | Title               | Is Tickets Enabled | Is Chat Enabled |
      | d1 | Ticket Department 1 | 1                  | 1               |

    When I send a PUT request to "/api/v2/<type>_departments/{d1}" with body:
    """
{
  "user_title": "user title for new department"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/<type>_departments/{d1}"
    Then the response status code should be 200
    And the JSON node "data.title" should be equal to "Ticket Department 1"
    And the JSON node "data.user_title" should be equal to "user title for new department"

    Examples:
      | type   |
      | ticket |
      | chat   |

  Scenario Outline: I delete department
    Given only the following Department records exist:
      | #  | Title               | Is Tickets Enabled | Is Chat Enabled |
      | d1 | Ticket Department 1 | 1                  | 1               |

    When I send a DELETE request to "/api/v2/<type>_departments/{d1}"
    Then the response status code should be 200

    Examples:
      | type   |
      | ticket |
      | chat   |
