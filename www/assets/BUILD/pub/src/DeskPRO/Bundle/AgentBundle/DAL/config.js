import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { UserChatRepository } from './Repositories/UserChatRepository';
import BlobRepository from './Repositories/BlobRepository';
import { AgentChatRepository } from './Repositories/AgentChatRepository';
import { PersonSettingRepository } from './Repositories/PersonSettingRepository';
import { ArticlePendingCreateRepository } from './Repositories/ArticlePendingCreateRepository';
import { ContentRepository } from './Repositories/ContentRepository';
import { CommentsRepository } from './Repositories/CommentsRepository';
import GuideRepository from './Repositories/GuideRepository';
import OnboardingRepository from './Repositories/OnboardingRepository';
import SnippetsRepository from './Repositories/SnippetsRepository';
import TicketRepository from './Repositories/TicketRepository';

export const repositoriesConfig = {
  AgentChat:            { type: 'api', url: '/agent_chats', repositoryClass: AgentChatRepository },
  Blob:                 { type: 'api', url: '/blobs', repositoryClass: BlobRepository },
  Content:              { type: 'factory', factory: () => new ContentRepository(api) },
  Comment:              { type: 'factory', factory: () => new CommentsRepository(api) },
  Guide:                { type: 'api', url: '/guides', repositoryClass: GuideRepository },
  Organization:         { type: 'api', url: '/organizations' },
  OrganizationLabels:   { type: 'api', url: '/organization_labels', allowAll: true },
  Onboarding:           { type: 'api', url: '/people/onboarding', repositoryClass: OnboardingRepository },
  Person:               { type: 'api', url: '/people' },
  PersonSetting:        { type: 'api', url: '/person_setting', repositoryClass: PersonSettingRepository },
  PersonLabels:         { type: 'api', url: '/person_labels', allowAll: true },
  Project:              { type: 'api', url: '/task_projects', allowAll: true },
  Snippets:             { type: 'api', url: '/snippets', repositoryClass: SnippetsRepository },
  Slas:                 { type: 'api', url: '/slas' },
  Timezone:             { type: 'api', url: '/timezones', allowAll: true },
  Topic:                { type: 'api', url: '/topics' },
  UserChat:             { type: 'api', url: '/user_chats', repositoryClass: UserChatRepository },
  ArticlePendingCreate: {
    type:            'api',
    url:             '/article_pending_creates',
    repositoryClass: ArticlePendingCreateRepository
  },
  VoiceAccount:     { type: 'api', url: '/voice_accounts', allowAll: true },
  VoiceQueue:       { type: 'api', url: '/voice_queues', allowAll: true },
  VoiceNumber:      { type: 'api', url: '/voice_numbers', allowAll: true },
  VoicePhoneCall:   { type: 'api', url: '/voice_phone_calls' },
  VoicemailRecord:  { type: 'api', url: '/voicemail_records', allowAll: true },
  Brands:           { type: 'api', url: '/brands', allowAll: true },
  Ticket:           { type: 'api', url: '/tickets', allowAll: true, repositoryClass: TicketRepository },
  TicketCategories: { type: 'api', url: '/ticket_categories', allowAll: true },
  TicketLabels:     { type: 'api', url: '/ticket_labels', allowAll: true },
  TicketMacros:     { type: 'api', url: '/ticket_macros', allowAll: true },
  TicketPriorities: { type: 'api', url: '/ticket_priorities', allowAll: true },
  TicketProducts:   { type: 'api', url: '/ticket_products', allowAll: true },
  TicketWorkflows:  { type: 'api', url: '/ticket_workflows', allowAll: true },
  EmailAccount:     { type: 'api', url: '/email_accounts', allowAll: true },
  UserGroups:       { type: 'api', url: '/user_groups', allowAll: true },
};
