import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { TicketFilterRepository } from './Repositories/TicketFilterRepository';
import { UserChatRepository } from './Repositories/UserChatRepository';
import { AgentChatRepository } from './Repositories/AgentChatRepository';
import { FeedbackRepository } from './Repositories/FeedbackRepository';
import { FeedbackCommentRepository } from './Repositories/FeedbackCommentRepository';
import { PersonSettingRepository } from './Repositories/PersonSettingRepository';
import { ArticlePendingCreateRepository } from './Repositories/ArticlePendingCreateRepository';
import { ContentRepository } from './Repositories/ContentRepository';
import { CommentsRepository } from './Repositories/CommentsRepository';

export const repositoriesConfig = {
  Ticket: { type: 'api', url: '/tickets' },
  TicketFilter: { type: 'api', url: '/ticket_filters', repositoryClass: TicketFilterRepository },
  UserChat: { type: 'api', url: '/user_chats', repositoryClass: UserChatRepository },
  AgentChat: { type: 'api', url: '/agent_chats', repositoryClass: AgentChatRepository },
  Feedback: { type: 'api', url: '/feedback', repositoryClass: FeedbackRepository },
  FeedbackComment: {
    type: 'api',
    url: '/feedback_comments',
    repositoryClass: FeedbackCommentRepository
  },
  Organization: { type: 'api', url: '/organizations' },
  Person: { type: 'api', url: '/people' },
  Timezone: { type: 'api', url: '/timezones', allowAll: true },
  FeedbackCategory: { type: 'api', url: '/feedback_categories' },
  FeedbackCommentCounter: { type: 'api', url: '/feedback_comments/counter' },
  Project: { type: 'api', url: '/projects', allowAll: true },
  TaskLabel: { type: 'api', url: '/task_labels', allowAll: true },
  TaskList: { type: 'api', url: '/task_lists', allowAll: true },
  PersonSetting: { type: 'api', url: '/person_setting', repositoryClass: PersonSettingRepository },
  ArticlePendingCreate: {
    type: 'api',
    url: '/article_pending_create',
    repositoryClass: ArticlePendingCreateRepository
  },
  Content: { type: 'factory', factory: () => new ContentRepository(api) },
  Comment: { type: 'factory', factory: () => new CommentsRepository(api) }
};
