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
  AgentChat:            { type: 'api', url: '/agent_chats', repositoryClass: AgentChatRepository },
  Content:              { type: 'factory', factory: () => new ContentRepository(api) },
  Comment:              { type: 'factory', factory: () => new CommentsRepository(api) },
  Feedback:             { type: 'api', url: '/feedback', repositoryClass: FeedbackRepository },
  FeedbackCategory:     { type: 'api', url: '/feedback_categories' },
  FeedbackComment:      { type: 'api', url: '/feedback_comments', repositoryClass: FeedbackCommentRepository },
  Organization:         { type: 'api', url: '/organizations' },
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
  }
};
