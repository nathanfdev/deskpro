import { createAction } from "Ampliflux";
import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import * as Feedback from "DeskPRO/Bundle/AgentBundle/Services/Api/Feedback";
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'

export const feedbackToValidate = createAction(
  "FEEDBACK_TO_VALIDATE",
  (trigger) => {
    Feedback.toValidate().then(
      (value) => trigger(value.getData())
    );
  }
);

export const commentsToReview = createAction(
  "FEEDBACK_COMMENTS_TO_REVIEW",
  (trigger) => {
    Feedback.commentsToReview().then(
      (value) => trigger(value.getData())
    );
  }
);

export const feedbackLabels = createAction(
  "FEEDBACK_LABELS",
  (trigger) => {
    Feedback.getLabels().then(
      (value) => trigger(value.getData())
    );
  }
);

export const feedbackTypes = createAction(
  "FEEDBACK_TYPES",
  (trigger) => {
    Feedback.getTypes().then(
      (value) => trigger(value.getData())
    );
  }
);

export const feedbackCustomCategories = createAction(
  "FEEDBACK_CUSTOM_CATEGORIES",
  (trigger) => {
    Feedback.getCustomCategories().then(
      (value) => trigger(value.getData())
    );
  }
);

export const feedbackNew = createAction(
  "FEEDBACK_NEW_STATUS",
  (trigger) => {
    Feedback.getNew().then(
      (value) => trigger(value.getData())
    );
  }
);


export const feedbackActiveStatus = createAction(
  "FEEDBACK_ACTIVE_STATUS",
  (trigger) => {
    Feedback.getActive().then(
      (value) => trigger(value.getData())
    );
  }
);

export const feedbackClosedStatus = createAction(
  "FEEDBACK_CLOSED_STATUS",
  (trigger) => {
    Feedback.getClosed().then(
      (value) => trigger(value.getData())
    );
  }
);

export const feedbackHiddenStatus = createAction(
  "FEEDBACK_HIDDEN_STATUS",
  (trigger) => {
    Feedback.getHidden().then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadFeedbackList = createAction(
  "FEEDBACK_LIST",
  (trigger, query, filters, sort, order) => {
    Feedback.getList(query, filters, sort, order).then(
      (value) => trigger(value.getData())
    )
  }
);

export const changeQueryState = createAction(
  "FEEDBACK_CHANGE_QUERY",
  (trigger, query, sort, order, filters) => {
    trigger(query);
    trigger(loadFeedbackList(query, sort, order, filters));
  }
);

export const getFilterValues = createAction(
  "FEEDBACK_SELECT_FILTER",
  (trigger, filterName) => {
    Feedback.getFilterValues(filterName).then(
      (value) => trigger(value.getData())
    )
  }
);

export const setFilterValue = createAction(
  "FEEDBACK_SET_FILTER_VALUE",
  (trigger, filter, value) => {
    trigger({filter: filter, value: value});
  }
);

export const resetFilterValue = createAction(
  "FEEDBACK_RESET_FILTER_VALUE"
);

export const resetFilters = createAction(
  "FEEDBACK_RESET_FILTERS",
  (trigger, filterAlias, filterName) => {
    trigger({alias: filterAlias, name: filterName, value: ''});
  });

export const setTableSort = createAction(
  "FEEDBACK_SET_TABLE_SORT",
  (trigger, query, sort, order, filters) => {
    trigger({sort: sort, order: order});
    trigger(loadFeedbackList(query, sort, order, filters));
  });

export const toggleOrder = createAction(
  "FEEDBACK_TOGGLE_ORDER",
  (trigger, query, sort, order, filters) => {
    trigger();
    let newOrder = order === constants.ORDER_DESC ? constants.ORDER_ASC : constants.ORDER_DESC;
    trigger(loadFeedbackList(query, sort, newOrder, filters));
  });

export const toggleSort = createAction(
  "FEEDBACK_TOGGLE_SORT",
  (trigger, query, sort, sortName, order, filters) => {
    trigger({sort: sort, sortName: sortName});
    trigger(loadFeedbackList(query, sort, order, filters));
  });

export const storeDisplayFieldsToPersonSetting = createAction(
  "FEEDBACK_DISPLAY_FIELD_TO_PERSON_SETTING",
  (trigger, displayFields) => {
    Feedback.postDisplayFieldsToPersonSetting('feedback_display_fields', displayFields).then(
      (value) => trigger(value.getData())
    )
  });

export const getDisplayFieldsFromPersonSetting = createAction(
  "FEEDBACK_DISPLAY_FIELD_TO_PERSON_SETTING",
  (trigger) => {
    Feedback.getDisplayFieldsFromPersonSetting('feedback_display_fields').then(
      (value) => trigger(value.getData())
    )
  });

export const changeDisplayFieldsStatus = createAction(
  "FEEDBACK_DISPLAY_FIELD_STATUS",
  (trigger, type, field, status, query, sort, order, filters, listViewFields, tableViewFields) => {
    trigger({type: type, field: field, status: status});
    trigger(storeDisplayFieldsToPersonSetting([{listViewFields: listViewFields, tableViewFields: tableViewFields}]));
    trigger(loadFeedbackList(query, sort, order, filters));
  });
