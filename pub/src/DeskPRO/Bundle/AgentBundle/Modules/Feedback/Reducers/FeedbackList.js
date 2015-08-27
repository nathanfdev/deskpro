import * as FeedbackListActions from "../Actions/FeedbackListActions.js";
import * as peopleActions from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/Actions/PeopleActions';
import { Reducer } from "Ampliflux/reducers";

export default class FeedbackList extends Reducer {
    getInitialState() {
        return {
            view: 'list',
            query: {awaiting_validation: 1},
            filters: {},
            sort: {
                sort: 'date_created',
                order: 'asc'
            },
            sortName: 'Date',
            toValidateCount: 0,
            commentsToReviewCount: 0,
            labels: [/* string */],
            types: [/* {title, value} */],
            customCategories: [/* {title, value} */],
            feedback: [],
            statuses: {
                new: 0,
                active: {
                    total: 0,
                    statuses: [/* {count, group} */]
                },
                closed: {
                    total: 0,
                    statuses: [/* {count, group} */]
                },
                hidden: {
                    total: 0,
                    statuses: [/* {count, group} */]
                }
            }
        };
    }


    toValidate(prev, {payload}) {
        const next = {...prev};
        next.toValidateCount = payload.data.count;
        return next;
    }

    commentsToReview(state, action) {
        return {
            ...state,
            commentsToReviewCount: action.payload.data.count
        };
    }

    labels(state, action) {
        return {
            ...state,
            labels: action.payload.data
        };
    }

    types(state, action) {
        return {
            ...state,
            types: action.payload.data
        };
    }

    customCategories(prev, {payload}) {
        const next = {...prev};
        next.customCategories = payload.data.nested;
        return next;
    }

    new(prev, {payload}) {
        const next = {...prev};
        next.statuses.new = payload.data.count;

        return next;
    }

    active(prev, {payload}) {
        const next = {...prev};
        next.statuses.active.statuses = payload.data.nested;
        next.statuses.active.total = payload.data.count;
        return next;
    }

    closed(prev, {payload}) {
        const next = {...prev};
        next.statuses.closed.statuses = payload.data.nested;
        next.statuses.closed.total = payload.data.count;
        return next;
    }

    hidden(prev, {payload}) {
        const next = {...prev};
        next.statuses.hidden.statuses = payload.data.nested;
        next.statuses.hidden.total = payload.data.count;
        return next;
    }

    getList(prev, {payload}) {
        const next = {...prev};
        next.feedback = payload.data;
        return next;
    }

    changeQuery(prev, {payload}) {
        const next = {...prev};
        next.query = payload;
        return next;
    }

    switchView(prev) {
        const next = {...prev};
        next.view = prev.view === 'list' ? 'table' : 'list';
        return next;
    }


    registerHandlers() {
        this
            .r(FeedbackListActions.feedbackToValidate, this.toValidate)
            .r(FeedbackListActions.feedbackLabels, this.labels)
            .r(FeedbackListActions.feedbackTypes, this.types)
            .r(FeedbackListActions.feedbackCustomCategories, this.customCategories)
            .r(FeedbackListActions.feedbackNew, this.new)
            .r(FeedbackListActions.feedbackActiveStatus, this.active)
            .r(FeedbackListActions.feedbackClosedStatus, this.closed)
            .r(FeedbackListActions.feedbackHiddenStatus, this.hidden)
            .r(FeedbackListActions.changeQueryState, this.changeQuery)
            .r(FeedbackListActions.switchView, this.switchView)
            .r(FeedbackListActions.loadFeedbackList, this.getList);
    }

}
