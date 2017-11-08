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

export const repositoriesConfig = {
  AgentChat:            { type: 'api', url: '/agent_chats', repositoryClass: AgentChatRepository },
  Blob:                 { type: 'api', url: '/blobs', repositoryClass: BlobRepository },
  Content:              { type: 'factory', factory: () => new ContentRepository(api) },
  Comment:              { type: 'factory', factory: () => new CommentsRepository(api) },
  Guide:                { type: 'api', url: '/guides', repositoryClass: GuideRepository },
  Organization:         { type: 'api', url: '/organizations' },
  Onboarding:           { type: 'api', url: '/people/onboarding', repositoryClass: OnboardingRepository },
  Person:               { type: 'api', url: '/people' },
  PersonSetting:        { type: 'api', url: '/person_setting', repositoryClass: PersonSettingRepository },
  Project:              { type: 'api', url: '/task_projects', allowAll: true },
  Snippets:             { type: 'api', url: '/snippets', repositoryClass: SnippetsRepository },
  Timezone:             { type: 'api', url: '/timezones', allowAll: true },
  Topic:                { type: 'api', url: '/topics' },
  UserChat:             { type: 'api', url: '/user_chats', repositoryClass: UserChatRepository },
  ArticlePendingCreate: {
    type:            'api',
    url:             '/article_pending_creates',
    repositoryClass: ArticlePendingCreateRepository
  },
  VoiceQueue:      { type: 'api', url: '/voice_queues', allowAll: true },
  VoiceNumber:     { type: 'api', url: '/voice_numbers', allowAll: true },
  VoicePhoneCall:  { type: 'api', url: '/voice_phone_calls' },
  VoicemailRecord: { type: 'api', url: '/voicemail_records', allowAll: true },
  Brands:          { type: 'api', url: '/brands', allowAll: true },
  TicketMacros:    { type: 'api', url: '/ticket_macros', allowAll: true },
};
