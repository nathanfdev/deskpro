import { crmNavInitialState as initial } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/Reducers/nav';

export const crmNavInitialState = {CRM: {nav: initial}};

export const crmNavLoadingState = {CRM: {nav: {async: {done: false}}}};

export const crmNavDemoState = {
  CRM: {
    nav: {
      "async": {
        "done": true
      },
      "organizations": {
        "count": 79
      },
      "users": {
        "count": 511,
        "nested": [
          {"count": 371, "id": 0, "type": "user_group", "title": ""},
          {"count": 81, "id": 5, "type": "user_group", "title": "VIP"},
          {"count": 80, "id": 6, "type": "user_group", "title": "Extra Priv"},
          {"count": 89, "id": 7, "type": "user_group", "title": "Beta Users"}
        ],
        "grouped_by": "user_group"
      },
      "agents": {
        "count": 12,
        "nested": [
          {"count": 12, "id": 0, "type": "agent_team", "title": ""},
          {"count": 42, "id": 1, "type": "agent_team", "title": "Support"},
          {"count": 13, "id": 2, "type": "agent_team", "title": "Sales"},
          {"count": 13, "id": 3, "type": "agent_team", "title": "Whatever"}
        ],
        "grouped_by": "agent_team"
      },
      "organizationLabels": [
        {"label_type": "organization", "label": "ankunding-wolff", "color": "#8ee27d", "total": 7},
        {"label_type": "organization", "label": "carrot ltd", "color": "#4c060f", "total": 13},
        {"label_type": "organization", "label": "beatty, dickens and wiza", "color": "#3dcfab", "total": 23},
        {"label_type": "organization", "label": "beatty, schinner and lind", "color": "#371e02", "total": 0},
        {"label_type": "organization", "label": "show inc", "color": "#c2de41", "total": 10}
      ],
      "personLabels": [
        {"label_type": "people", "label": "ankunding-wolff", "color": "#8ee27d", "total": 7},
        {"label_type": "people", "label": "carrot ltd", "color": "#4c060f", "total": 13},
        {"label_type": "people", "label": "beatty, dickens and wiza", "color": "#3dcfab", "total": 23},
        {"label_type": "people", "label": "beatty, schinner and lind", "color": "#371e02", "total": 0},
        {"label_type": "people", "label": "show inc", "color": "#c2de41", "total": 10}
      ]
    }
  }
};
