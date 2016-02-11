import { TicketFilterRepository } from './Repositories/TicketFilterRepository';
import { ChatRepository } from './Repositories/ChatRepository';
import { FeedbackRepository } from './Repositories/FeedbackRepository';
import { FeedbackCommentRepository } from './Repositories/FeedbackCommentRepository';

export const repositoriesConfig = {
  Ticket:           {type: 'api', url: '/tickets'},
  TicketFilter:     {type: 'api', url: '/ticket_filters', repositoryClass: TicketFilterRepository},
  Chat:             {type: 'api', url: '/user_chats', repositoryClass: ChatRepository},
  Feedback:         {type: 'api', url: '/feedback', repositoryClass: FeedbackRepository},
  FeedbackComment:  {type: 'api', url: '/feedback_comments', repositoryClass: FeedbackCommentRepository},
  Organization:     {type: 'api', url: '/organizations'},
  Person:           {type: 'api', url: '/people'},
  Timezone:         {type: 'api', url: '/timezones', allowAll: true},
  FeedbackCategory: {type: 'api', url: '/feedback_categories'},
  FeedbackCommentCounter: {type: 'api', url: '/feedback_comments_counter'}
};
