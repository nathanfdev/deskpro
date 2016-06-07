import { createReducer } from 'DeskPRO/Component/Ampliflux';
import { async, setFullPayload, setValue } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import * as actions from '../Actions/publishListActions';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

const initialState = {
  async: { done: true },

  // view mode (table or list)
  view: constants.VIEW_MODE_CARD,
  // currently viewed list GET parameters map

  fields: {
    articles: {
      [constants.VIEW_MODE_CARD]: [
        { id: 'category', title: 'Category', visible: true },
        { id: 'date_created', title: 'Date Created', visible: true },
        { id: 'labels', title: 'Labels', visible: true }
      ],

      [constants.VIEW_MODE_TABLE]: [
        { id: 'id', title: 'Id', visible: true },
        { id: 'title', title: 'Title', visible: true },
        { id: 'person', title: 'Author', visible: true },
        { id: 'content', title: 'Content', visible: true },
        { id: 'status', title: 'Status', visible: true },
        { id: 'date_created', title: 'Date Created', visible: true }
      ]
    },

    news: {
      [constants.VIEW_MODE_CARD]: [
        { id: 'category', title: 'Category', visible: true },
        { id: 'date_created', title: 'Date Created', visible: true },
        { id: 'labels', title: 'Labels', visible: true }
      ],

      [constants.VIEW_MODE_TABLE]: [
        { id: 'id', title: 'Id', visible: true },
        { id: 'title', title: 'Title', visible: true },
        { id: 'person', title: 'Author', visible: true },
        { id: 'content', title: 'Content', visible: true },
        { id: 'status', title: 'Status', visible: true },
        { id: 'date_created', title: 'Date Created', visible: true }
      ]
    },

    downloads: {
      [constants.VIEW_MODE_CARD]: [
        { id: 'category', title: 'Category', visible: true },
        { id: 'date_created', title: 'Date Created', visible: true },
        { id: 'labels', title: 'Labels', visible: true }
      ],

      [constants.VIEW_MODE_TABLE]: [
        { id: 'id', title: 'Id', visible: true },
        { id: 'title', title: 'Title', visible: true },
        { id: 'person', title: 'Author', visible: true },
        { id: 'content', title: 'Content', visible: true },
        { id: 'status', title: 'Status', visible: true },
        { id: 'date_created', title: 'Date Created', visible: true }
      ]
    }
  },

  currentListParams: {
    order_by:  'date_created',
    order_dir: constants.ORDER_DESC,
    // which list is displayed
    content:   'articles'
  }
};

export default createReducer(initialState, {
  [actions.load]: async(
    {
      success: (state, payload) =>
                 state.set('elements', payload.ids).set('pagination', payload.pagination),

      start: setValue('async.done', false),
      done:  setValue('async.done', true)
    }
  ),

  [actions.setParams]: setFullPayload('currentListParams')
});
