@new
Feature: Brand Delete

  Background:
    Given I'm authenticated as admin
    And agent and user exist
    And I have only default brand
    And the following Brand records exist:
      | #  | Name    |
      | b1 | Brand 1 |
      | b2 | Brand 2 |
      | b3 | Brand 3 |

    Scenario: tickets change brand to default one on delete brand
      Given only the following Department records exist:
        | #  | Title               | Brands                 | Is Tickets Enabled | Is Chat Enabled |
        | d1 | Ticket Department 1 | [{defaultBrand}, {b1}] | 1                  | 0               |
      And only the following Ticket records exist:
        | #  | Subject  | Person | Agent   | Brand | Department |
        | t1 | Ticket 1 | {user} | {admin} | {b1}  | {d1}       |
        | t2 | Ticket 2 | {user} | {admin} | {b1}  | {d1}       |

      When I send a DELETE request to "/api/v2/brands/{b1}"
      Then the response status code should be 204

      When I send a GET request to "/api/v2/tickets?order_by=id&order_dir=asc"
      Then the response status code should be 200
      And the JSON node "data" should have 2 elements
      And the JSON node "data[0].id" should be equal to "{t1}"
      And the JSON node "data[0].brand" should be equal to "{defaultBrand}"
      And the JSON node "data[0].department" should be equal to "{d1}"
      And the JSON node "data[1].id" should be equal to "{t2}"
      And the JSON node "data[1].brand" should be equal to "{defaultBrand}"
      And the JSON node "data[1].department" should be equal to "{d1}"

    Scenario: tickets change brand and department to default ones on delete brand
      Given only the following Department records exist:
        | #  | Title               | Brands           | Is Tickets Enabled | Is Chat Enabled |
        | d1 | Ticket Department 1 | [{b1}]           | 1                  | 0               |
        | d2 | Ticket Department 2 | [{defaultBrand}] | 1                  | 0               |
      And only the following Ticket records exist:
        | #  | Subject  | Person | Agent   | Brand | Department |
        | t1 | Ticket 1 | {user} | {admin} | {b1}  | {d1}       |
        | t2 | Ticket 2 | {user} | {admin} | {b1}  | {d1}       |

      When I send a DELETE request to "/api/v2/brands/{b1}"
      Then the response status code should be 204

      When I send a GET request to "/api/v2/tickets?order_by=id&order_dir=asc"
      Then the response status code should be 200
      And the JSON node "data" should have 2 elements
      And the JSON node "data[0].id" should be equal to "{t1}"
      And the JSON node "data[0].brand" should be equal to "{defaultBrand}"
      And the JSON node "data[0].department" should be equal to "{d2}"
      And the JSON node "data[1].id" should be equal to "{t2}"
      And the JSON node "data[1].brand" should be equal to "{defaultBrand}"
      And the JSON node "data[1].department" should be equal to "{d2}"

    Scenario: chat conversations change brand to default one on delete brand
      Given only the following Department records exist:
        | #  | Title             | Brands                 | Is Tickets Enabled | Is Chat Enabled |
        | d1 | Chat Department 1 | [{defaultBrand}, {b1}] | 0                  | 1               |
      And only the following Chat records exist:
        | #  | Person | Agent   | Brand | Department |
        | c1 | {user} | {admin} | {b1}  | {d1}       |
        | c2 | {user} | {admin} | {b1}  | {d1}       |

      When I send a DELETE request to "/api/v2/brands/{b1}"
      Then the response status code should be 204

      When I send a GET request to "/api/v2/user_chats?order_by=id&order_dir=asc"
      Then the response status code should be 200
      And the JSON node "data" should have 2 elements
      And the JSON node "data[0].id" should be equal to "{c1}"
      And the JSON node "data[0].brand" should be equal to "{defaultBrand}"
      And the JSON node "data[0].department" should be equal to "{d1}"
      And the JSON node "data[1].id" should be equal to "{c2}"
      And the JSON node "data[1].brand" should be equal to "{defaultBrand}"
      And the JSON node "data[1].department" should be equal to "{d1}"

    Scenario: chat conversations change brand and department to default ones on delete brand
      Given only the following Department records exist:
        | #  | Title             | Brands           | Is Tickets Enabled | Is Chat Enabled |
        | d1 | Chat Department 1 | [{b1}]           | 0                  | 1               |
        | d2 | Chat Department 2 | [{defaultBrand}] | 0                  | 1               |
      And only the following Chat records exist:
        | #  | Person | Agent   | Brand | Department |
        | c1 | {user} | {admin} | {b1}  | {d1}       |
        | c2 | {user} | {admin} | {b1}  | {d1}       |

      When I send a DELETE request to "/api/v2/brands/{b1}"
      Then the response status code should be 204

      When I send a GET request to "/api/v2/user_chats?order_by=id&order_dir=asc"
      Then the response status code should be 200
      And the JSON node "data" should have 2 elements
      And the JSON node "data[0].id" should be equal to "{c1}"
      And the JSON node "data[0].brand" should be equal to "{defaultBrand}"
      And the JSON node "data[0].department" should be equal to "{d2}"
      And the JSON node "data[1].id" should be equal to "{c2}"
      And the JSON node "data[1].brand" should be equal to "{defaultBrand}"
      And the JSON node "data[1].department" should be equal to "{d2}"
