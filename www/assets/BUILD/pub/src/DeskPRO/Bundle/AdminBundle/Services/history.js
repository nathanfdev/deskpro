import { createMemoryHistory } from 'react-router';
import { createHashHistory } from 'history';

const hashHistory = createHashHistory();

export const history = createMemoryHistory();
export const replaceRoute = (route) => {
  hashHistory.replace(route);
};

export const openTicket = (ticketId) => {
  if (window.parent || window.parent.DP_FRAME_OVERLAYS || window.parent.DP_FRAME_OVERLAYS.admin) {
    replaceRoute(`/go_to_agent/#agent/tickets/${ticketId}`);
  } else {
    replaceRoute(`/go_to_agent/#agent/go/ticket/${ticketId}`);
  }
};

export const openPerson = (personId) => {
  if (window.parent || window.parent.DP_FRAME_OVERLAYS || window.parent.DP_FRAME_OVERLAYS.admin) {
    replaceRoute(`/go_to_agent/#agent/people/${personId}`);
  } else {
    replaceRoute(`/go_to_agent/#agent/go/person/${personId}`);
  }
};

export const openTarget = (target) => {
  const type = target.get('type');
  const id = target.get('id');

  if (type === 'agent') {
    openPerson(id);
  } else if (type === 'queue') {
    replaceRoute(`/voice_channel/queues/${id}`);
  } else if (type === 'auto_attendant') {
    replaceRoute(`/voice_channel/auto_attendants/${id}`);
  }
};
