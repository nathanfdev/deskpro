@New
Feature: /person/{id}/approval_responses endpoint
  To CRUD DeskPRO approval responses for a person
  As an API user
  I want an API endpoint

  Background:
    Given no ApprovalTemplate records exist
    And no TicketApproval records exist
    And no ApprovalResponse records exist
    And no ApprovalType records exist
    And agent and user exist
    And only the following Ticket records exist:
      | #  | Subject  | Person |
      | t1 | Ticket 1 | {user} |

    And only the following ApprovalType records exist:
      | #      | name           | description    | isDeleted |
      | atype1 | Approval Type 1 | Description 1 | false     |

    And the following ApproverSelectionCriteria objects exist:
      | #      | canSelectTicketUser | canSelectOrganizationManagers | canSelectFromAllAgents | selectFromPeople | minNumberOfApprovers |
      | ac1    | 1                   | 1                             | 1                      | [1,2]            | 1                    |
      | ac2    | 1                   | 1                             | 1                      | []               | 1                    |

    And the following SelectedApprovers objects exist:
      | #      | hasTicketUser | hasOrganizationManagers | hasAllAgents | people |
      | sa1    | 1             | 0                       | 0            | [1]    |

    And only the following ApprovalTemplate records exist:
      | #   | name    | description      | type     | requiredApprovals | requiredRejections | canApproversViewSubject | canChooseApprovers | selectedApprovers | approverSelectionCriteria |
      | at1 | Templ 1 | Approval Templ 1 | {atype1} | 1                 | 4                  | 1                       | 0                  | {sa1}             |                           |

    And only the following TicketApproval records exist:
      | #   | ticket | template | approvers         | name               | type     | description | status    |
      | ta1 | {t1}   | {at1}    | [{user}, {agent}] | Ticket approval 01 | {atype1} | TA 01       | pending   |

    And only the following ApprovalResponse records exist:
      | #   | vote | message                      | approval | approver |
      | ar1 | 1    | Approval response message 01 | {ta1}    | {user}   |
      | ar2 | -1   | Approval response message 02 | {ta1}    | {user}   |

  Scenario: I GET a list of approval responses for a person without authentication
    When I send a GET request to "/api/v2/person/{user}/approval_responses"
    Then the response status code should be 401

  Scenario: I GET a list of approval responses for a person
    Given I'm authenticated as "agent"
    When I send a GET request to "/api/v2/person/{user}/approval_responses"
    Then the response status code should be 200
    And the JSON node "meta.pagination.total" should be equal to "2"
    And the JSON node "data[0].id" should be equal to "{ar1}"
    And the JSON node "data[0].vote_type" should be equal to "approve"
    And the JSON node "data[0].message" should be equal to "Approval response message 01"
    And the JSON node "data[1].id" should be equal to "{ar2}"
    And the JSON node "data[1].vote_type" should be equal to "reject"
    And the JSON node "data[1].message" should be equal to "Approval response message 02"

  Scenario: I GET a list of approval response counts for a person without authentication
    When I send a GET request to "/api/v2/person/{user}/approval_responses/counts"
    Then the response status code should be 401

  Scenario: I GET a list of approval response counts for a person
    Given I'm authenticated as "agent"
    When I send a GET request to "/api/v2/person/{user}/approval_responses/counts"
    Then the response status code should be 200
    And the JSON node "data.count" should be equal to "2"
