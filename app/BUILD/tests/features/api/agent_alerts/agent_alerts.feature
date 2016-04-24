Feature: /me/notifications endpoint
  To retrieve DeskPRO agent alerts
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated
    And I prepare notifications data

  Scenario: I get notifications
    When I send a GET request to "/api/v2/me/notifications"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should have 4 elements
    And the JSON node "linked.person" should not exist
    And the JSON node "linked.ticket" should not exist

    And the JSON node "data[0].uuid" should be equal to 4
    And the JSON node "data[0].type" should contain "notifications.tickets.new_message.user_reply"
    And the JSON node "data[0].is_dismissed" should be equal to 0
    And the JSON node "data[0].date_created" should exist
    And the JSON node "data[0].data.ticket" should be equal to 569
    And the JSON node "data[0].data.performer" should be equal to 530
    And the JSON node "data[0].data.notification.title" should contain "Test Message #0510"
    And the JSON node "data[0].data.notification.summary" should be equal to "New user reply by User2 (user2@foobar.com)"

    And the JSON node "data[1].uuid" should be equal to 3
    And the JSON node "data[1].type" should contain "notifications.tickets.new_ticket"
    And the JSON node "data[1].is_dismissed" should be equal to 0
    And the JSON node "data[1].date_created" should exist
    And the JSON node "data[1].data.ticket" should be equal to 569
    And the JSON node "data[1].data.performer" should be equal to 530
    And the JSON node "data[1].data.notification.title" should contain "Test Message #0510"
    And the JSON node "data[1].data.notification.summary" should be equal to "New ticket by User2 (user2@foobar.com)"

    And the JSON node "data[2].uuid" should be equal to 2
    And the JSON node "data[2].type" should contain "notifications.tickets.new_ticket"
    And the JSON node "data[2].is_dismissed" should be equal to 0
    And the JSON node "data[2].date_created" should exist
    And the JSON node "data[2].data.ticket" should be equal to 568
    And the JSON node "data[2].data.performer" should be equal to 529
    And the JSON node "data[2].data.notification.title" should contain "Test Message #0509"
    And the JSON node "data[2].data.notification.summary" should be equal to "New ticket by User1 (user1@foobar.com)"

    And the JSON node "data[3].uuid" should be equal to 1
    And the JSON node "data[3].type" should contain "notifications.tickets.new_ticket"
    And the JSON node "data[3].is_dismissed" should be equal to 0
    And the JSON node "data[3].date_created" should exist
    And the JSON node "data[3].data.ticket" should be equal to 567
    And the JSON node "data[3].data.performer" should be equal to 528
    And the JSON node "data[3].data.notification.title" should contain "Test Message #0306"
    And the JSON node "data[3].data.notification.summary" should be equal to "New ticket by User (user@foobar.com)"

  @basic
  Scenario: I try to get notifications with sideloads
    When I send a GET request to "/api/v2/me/notifications?include=person,ticket"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "linked.person" should have 6 elements
    And the JSON node "linked.person.530.id" should be equal to 530
    And the JSON node "linked.person.529.id" should be equal to 529
    And the JSON node "linked.person.528.id" should be equal to 528
    And the JSON node "linked.person.1.id" should be equal to 1
    And the JSON node "linked.person.2.id" should be equal to 2
    And the JSON node "linked.person.3.id" should be equal to 3
    And the JSON node "linked.ticket" should have 3 elements
    And the JSON node "linked.ticket.569.id" should be equal to 569
    And the JSON node "linked.ticket.569.subject" should be equal to "Ticket #569"
    And the JSON node "linked.ticket.568.id" should be equal to 568
    And the JSON node "linked.ticket.568.subject" should be equal to "Ticket #568"
    And the JSON node "linked.ticket.567.id" should be equal to 567
    And the JSON node "linked.ticket.567.subject" should be equal to "Ticket #567"

  Scenario: I dismiss set of alerts
    When I send a POST request to "/api/v2/me/notifications/dismiss" with body:
    """
{
  "alert_ids": [1,2,3]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/me/notifications"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should have 4 elements

    And the JSON node "data[0].is_dismissed" should be equal to 0
    And the JSON node "data[1].is_dismissed" should be equal to 1
    And the JSON node "data[2].is_dismissed" should be equal to 1
    And the JSON node "data[3].is_dismissed" should be equal to 1

  Scenario: I get notification counts
    When I send a GET request to "/api/v2/me/notifications/counts"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.count" should be equal to 4
    And the JSON node "data.nested[0].type" should be equal to "non_dismissed"
    And the JSON node "data.nested[0].count" should be equal to 1
    And the JSON node "data.nested[1].type" should be equal to "dismissed"
    And the JSON node "data.nested[1].count" should be equal to 3

  Scenario: I dismiss all notifications
    When I send a POST request to "/api/v2/me/notifications/dismiss/all"
    Then the response status code should be 204

    When I send a GET request to "/api/v2/me/notifications"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should have 4 elements

    And the JSON node "data[0].is_dismissed" should be equal to 1
    And the JSON node "data[1].is_dismissed" should be equal to 1
    And the JSON node "data[2].is_dismissed" should be equal to 1
    And the JSON node "data[3].is_dismissed" should be equal to 1
