import { createSelector } from 'reselect';
import { createPeopleRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/peopleSelectors';
import { createArticlesRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Publish/RecordStores/Selectors/articlesSelectors';
import { createNewsRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Publish/RecordStores/Selectors/newsSelectors';
import { createDownloadsRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Publish/RecordStores/Selectors/downloadsSelectors';

export const peopleSelector = createSelector(
  createPeopleRequestSelectors('publish').recordsSel,
    people => people
);

export const articlesRecordsSelector = createSelector(
  createArticlesRequestSelectors('publish').recordsSel,
    articles => articles
);

export const newsRecordsSelector = createSelector(
  createNewsRequestSelectors('publish').recordsSel,
    news => news
);

export const downloadsRecordsSelector = createSelector(
  createDownloadsRequestSelectors('publish').recordsSel,
    downloads => downloads
);
