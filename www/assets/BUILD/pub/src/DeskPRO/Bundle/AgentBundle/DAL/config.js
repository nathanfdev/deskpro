import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { TicketFilterRepository } from './Repositories/TicketFilterRepository';
import { UserChatRepository } from './Repositories/UserChatRepository';
import BlobRepository from './Repositories/BlobRepository';
import { AgentChatRepository } from './Repositories/AgentChatRepository';
import { FeedbackRepository } from './Repositories/FeedbackRepository';
import { FeedbackCommentRepository } from './Repositories/FeedbackCommentRepository';
import { PersonSettingRepository } from './Repositories/PersonSettingRepository';
import { ArticlePendingCreateRepository } from './Repositories/ArticlePendingCreateRepository';
import { ContentRepository } from './Repositories/ContentRepository';
import { CommentsRepository } from './Repositories/CommentsRepository';
import ManualRepository from './Repositories/ManualRepository';
import OnboardingRepository from './Repositories/OnboardingRepository';

export const repositoriesConfig = {
  AgentChat:            { type: 'api', url: '/agent_chats', repositoryClass: AgentChatRepository },
  Blob:                 { type: 'api', url: '/blobs', repositoryClass: BlobRepository },
  Content:              { type: 'factory', factory: () => new ContentRepository(api) },
  Comment:              { type: 'factory', factory: () => new CommentsRepository(api) },
  Feedback:             { type: 'api', url: '/feedback', repositoryClass: FeedbackRepository },
  FeedbackCategory:     { type: 'api', url: '/feedback_categories' },
  FeedbackComment:      { type: 'api', url: '/feedback_comments', repositoryClass: FeedbackCommentRepository },
  Manual:               { type: 'api', url: '/content/manuals', repositoryClass: ManualRepository },
  ManualTopic:          { type: 'api', url: '/content/manual_topics' },
  Organization:         { type: 'api', url: '/organizations' },
  Onboarding:           { type: 'api', url: '/people/onboarding', repositoryClass: OnboardingRepository },
  Person:               { type: 'api', url: '/people' },
  PersonSetting:        { type: 'api', url: '/person_setting', repositoryClass: PersonSettingRepository },
  Project:              { type: 'api', url: '/task_projects', allowAll: true },
  Tasks:                { type: 'api', url: '/tasks' },
  TaskLabel:            { type: 'api', url: '/task_labels', allowAll: true },
  TaskList:             { type: 'api', url: '/task_lists', allowAll: true },
  Ticket:               { type: 'api', url: '/tickets' },
  TicketFilter:         { type: 'api', url: '/new/ticket_filters', repositoryClass: TicketFilterRepository },
  Timezone:             { type: 'api', url: '/timezones', allowAll: true },
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
  Brands:          { type: 'api', url: '/brands', allowAll: true }
};
