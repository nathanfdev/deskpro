import EmailTemplatesRepository from './Repositories/EmailTemplatesRepository';
import EmailAccountRepository from './Repositories/EmailAccountRepository';
import LanguagesRepository from './Repositories/LanguagesRepository';
import TicketsRepository from './Repositories/TicketsRepository';

export const repositoriesConfig = {
  EmailTemplates:     { type: 'api', url: '/email_templates', repositoryClass: EmailTemplatesRepository },
  EmailAccounts:      { type: 'api', url: '/email_accounts', repositoryClass: EmailAccountRepository },
  Languages:          { type: 'api', url: '/languages', repositoryClass: LanguagesRepository },
  Person:             { type: 'api', url: '/people', allowAll: false },
  Ticket:             { type: 'api', url: '/people', allowAll: false },
  TicketDepartment:   { type: 'api', url: '/ticket_departments', allowAll: true },
  ChatDepartment:     { type: 'api', url: '/chat_departments', allowAll: true },
  AgentTeam:          { type: 'api', url: '/agent_teams', allowAll: true },
  Tickets:            { type: 'api', url: '/tickets', repositoryClass: TicketsRepository },
  VoiceAccount:       { type: 'api', url: '/voice_accounts', allowAll: true },
  VoiceNumber:        { type: 'api', url: '/voice_numbers', allowAll: true },
  VoiceQueue:         { type: 'api', url: '/voice_queues', allowAll: true },
  VoiceAutoAttendant: { type: 'api', url: '/voice_auto_attendants', allowAll: true },
  VoicePhoneCall:     { type: 'api', url: '/voice_phone_calls', allowAll: false },
  OAuthClient:        { type: 'api', url: '/oauth_clients', allowAll: true },
  ImportLog:          { type: 'api', url: '/importer_logs', allowAll: true },
  UserChatQueue:      { type: 'api', url: '/user_chat_queues', allowAll: true },
  UserGroup:          { type: 'api', url: '/user_groups', allowAll: true },
  AgentGroup:         { type: 'api', url: '/agent_groups', allowAll: true },
};

export default repositoriesConfig;
