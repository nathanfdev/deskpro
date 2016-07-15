import { ticketsNavInitialState as initial } from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/Reducers/nav';

export const ticketsNavInitialState = {Tickets: {nav: initial}};

export const ticketsNavLoadingState = {Tickets: {nav: {async: {done: false}}}};

export const ticketsNavDemoState = {
  Tickets: {
    nav: {
      "filterSetsCount": [
        {
          "count": 330,
          "nested": [
            {
              "count": 15,
              "nested": [
                {"count": 5, "id": 5, "type": "department", "title": "Widgets"},
                {"count": 3, "id": 2, "type": "department", "title": "Sales"},
                {"count": 3, "id": 1, "type": "department", "title": "Support"},
                {"count": 2, "id": 9, "type": "department", "title": "Hotdogs"},
                {"count": 1, "id": 8, "type": "department", "title": "Control"},
                {"count": 1, "id": 7, "type": "department", "title": "Regulation"}
              ],
              "id": 10,
              "type": "filter",
              "title": "My Tickets",
              "grouped_by": "department"
            },
            {
              "count": 0,
              "id": 11,
              "type": "filter",
              "title": "My Team's Tickets"
            },
            {
              "count": 31,
              "nested": [
                {"count": 21, "id": 0, "type": "organization", "title": ""},
                {"count": 10, "id": 76, "type": "organization", "title": "Mana Publishing"}
              ],
              "id": 12,
              "type": "filter",
              "title": "Tickets I Follow",
              "grouped_by": "organization"
            },
            {
              "count": 267,
              "id": 13,
              "type": "filter",
              "title": "All"
            }
          ],
          "id": 1,
          "type": "ticket_filter_set",
          "title": "Inbox",
          "grouped_by": "filter"
        },
        {
          "count": 1641,
          "nested": [
            {
              "count": 267,
              "id": 9,
              "type": "filter",
              "title": "Aging"
            },
            {
              "count": 267,
              "nested": [
                {"count": 92, "id": 514, "type": "person", "title": "Joe Kool"},
                {"count": 3, "id": 288, "type": "person", "title": "Manuela Jast"},
                {"count": 2, "id": 519, "type": "person", "title": "Mara Reilly"}
              ],
              "id": 20,
              "type": "filter",
              "title": "New (opened today)"
            },
            {
              "count": 7,
              "id": 21,
              "type": "filter",
              "title": "My Awaiting User"
            },
            {
              "count": 142,
              "nested": [
                {"count": 13, "id": 1, "type": "language", "title": "English"},
                {"count": 51, "id": 2, "type": "language", "title": "German"},
                {"count": 46, "id": 6, "type": "language", "title": "Fran\u00e7ais"},
                {"count": 39, "id": 5, "type": "language", "title": "\u0627\u0644\u0639\u0631\u0628\u064a\u0629"},
                {"count": 39, "id": 3, "type": "language", "title": "Polski"}
              ],
              "id": 22,
              "type": "filter",
              "title": "All Awaiting User"
            }
          ],
          "id": 2,
          "type": "ticket_filter_set",
          "title": "All Tickets",
          "grouped_by": "filter"
        }
      ],
      "filters": [
        {"title": "My Tickets", "id": 1},
        {"title": "My Team's Tickets", "id": 2},
        {"title": "Tickets I Follow", "id": 3},
        {"title": "Unassigned", "id": 4},
        {"title": "All", "id": 5},
        {"title": "Mine On Hold", "id": 6},
        {"title": "All On Hold", "id": 7},
        {"title": "My Recent Activity", "id": 8},
        {"title": "Aging", "id": 9},
        {"title": "New (opened today)", "id": 10}
      ],
      "labels": [
        {"label_type": "tickets", "label": "ankunding-wolff", "color": "#8ee27d", "total": 7},
        {"label_type": "tickets", "label": "carrot ltd", "color": "#4c060f", "total": 13},
        {"label_type": "tickets", "label": "beatty, dickens and wiza", "color": "#3dcfab", "total": 23},
        {"label_type": "tickets", "label": "beatty, schinner and lind", "color": "#371e02", "total": 0},
        {"label_type": "tickets", "label": "show inc", "color": "#c2de41", "total": 10}
      ],
      "starsCount": [
        {"count": 12, "id": 1, "type": "ticket_star", "title": "Blue"},
        {"count": 19, "id": 2, "type": "ticket_star", "title": "Libero"},
        {"count": 8, "id": 3, "type": "ticket_star", "title": "Orange"}
      ],
      "editedFilterId": null,
      "async": {
        "done": true,
        "filtersLoading": [2]
      }
    }
  }
};
