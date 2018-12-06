@new
Feature:
  To export filtered list of tickets to CSV file
  As an API user
  I need /tickets/csv endpoint

  Background:
    Given I'm authenticated as agent
    And I have permissions to use tickets
    And the following Organization records exist:
      | #         | Name                  |
      | microsoft | Microsoft Corporation |
    And only the following TicketStatus records exist:
      | #   | StatusType     | SysId        | Title    |
      | ts1 | hidden         | spam         | Spam     |
      | ts2 | hidden         | deleted      | Deleted  |
    And only the following Ticket records exist:
      | #       | Subject            | Agent   | Organization | Status         | TicketStatus  |
      | ticket1 | First Demo Ticket  | {agent} | {microsoft}  | awaiting_user  |               |
      | ticket2 | Second Demo Ticket | {agent} |              | awaiting_agent |               |
      | ticket3 | Third Demo Ticket  | {agent} | {microsoft}  | resolved       |               |
      | ticket4 | Fourth Demo Ticket | {agent} |              | archived       |               |
      | ticket5 | Fifth Demo Ticket  | {agent} |              | hidden         | {ts2}         |

  Scenario: I GET list of tickets in CSV format
    When I send a GET request to "/api/v2/tickets/csv?organization={microsoft}&status=resolved&count=200"
    Then the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].organization" should be equal to "Microsoft Corporation"
    And the JSON node "meta" should exist
