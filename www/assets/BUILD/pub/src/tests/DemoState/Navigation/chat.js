import { chatNavInitialState as initial } from 'DeskPRO/Bundle/AgentBundle/Modules/Chat/Reducers/nav';

export const chatNavInitialState = {Chat: {nav: initial}};

export const chatNavLoadingState = {Chat: {nav: {async: {done: false}}}};

export const chatNavDemoState = {
  Chat: {
    nav: {
      "async": {
        "done": true
      },
      "my": {
        "count": 3,
        "nested": [
          {"count": 7, "id": "this_month", "type": "date_period", "title": "This Month"},
          {"count": 25, "id": "last_month", "type": "date_period", "title": "Last Month"},
          {"count": 81, "id": "last_month", "type": "date_period", "title": "This Year"}
        ],
        "grouped_by": "date_period"
      },
      "all": {
        "count": 3,
        "nested": [
          {"count": 0, "id": "today", "type": "date_period", "title": "Today"},
          {"count": 12, "id": "this_month", "type": "date_period", "title": "This Month"},
          {"count": 54, "id": "last_month", "type": "date_period", "title": "Last Month"},
          {"count": 8, "id": "last_year", "type": "date_period", "title": "Last Year"}
        ],
        "grouped_by": "date_period"
      }
    }
  }
};
