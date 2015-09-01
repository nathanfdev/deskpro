import { createAction } from "Ampliflux/actions";
import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import * as Feedback from "DeskPRO/Bundle/AgentBundle/Services/Api/Feedback";

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
    (trigger, query, filters, sort) => {
        Feedback.getList(query, filters, sort).then(
            (value) => trigger(value.getData())
        )
    }
);

export const changeQueryState = createAction(
    "FEEDBACK_CHANGE_QUERY",
    (trigger, query) => {
        trigger(query);
    }
);

export const switchView = createAction(
    "FEEDBACK_SWITCH_VIEW",
    (trigger) => {
        trigger();
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
    "FEEDBACK_RESET_FILTER_VALUE",
    (trigger) => {
        trigger();
    });

export const resetFilters = createAction(
    "FEEDBACK_RESET_FILTERS",
    (trigger, filterAlias, filterName) => {
        trigger({alias: filterAlias, name: filterName, value: ''});
    });

export const setSort = createAction(
    "FEEDBACK_SET_SORT",
    (trigger, param, order) => {
        trigger({sort: param, order: order});
    });
