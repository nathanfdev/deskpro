export const repositoriesConfig = {
  EmailTemplates:     { type: 'api', url: '/email_templates/info' },
  Person:             { type: 'api', url: '/people', allowAll: false },
  Ticket:             { type: 'api', url: '/people', allowAll: false },
  TicketDepartment:   { type: 'api', url: '/ticket_departments', allowAll: true },
  AgentTeam:          { type: 'api', url: '/agent_teams', allowAll: true },
  VoiceAccount:       { type: 'api', url: '/voice_accounts', allowAll: true },
  VoiceNumber:        { type: 'api', url: '/voice_numbers', allowAll: true },
  VoiceQueue:         { type: 'api', url: '/voice_queues', allowAll: true },
  VoiceAutoAttendant: { type: 'api', url: '/voice_auto_attendants', allowAll: true },
  VoicePhoneCall:     { type: 'api', url: '/voice_phone_calls', allowAll: false },
  OAuthClient:        { type: 'api', url: '/oauth_clients', allowAll: true }
};

export default repositoriesConfig;
