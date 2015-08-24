import * as FeedbackListActions from "../Actions/FeedbackListActions.js";
import * as peopleActions from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/Actions/PeopleActions';
import { Reducer } from "Ampliflux/reducers";

export default class FeedbackList extends Reducer {
    getInitialState() {
        return {
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

    toValidate(state, action) {
        return {
            ...state,
            toValidateCount: action.payload.data.count
        };
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
        next.customCategories = payload.data.nested.counts;
        return next;
    }

    new(prev, {payload}) {
        const next = {...prev};
        next.statuses.new = payload.data.count;

        return next;
    }

    active(prev, {payload}) {
        const next = {...prev};
        next.statuses.active.statuses = payload.data.nested.counts;
        next.statuses.active.total = payload.data.count;
        return next;
    }

    closed(prev, {payload}) {
        const next = {...prev};
        next.statuses.closed.statuses = payload.data.nested.counts;
        next.statuses.closed.total = payload.data.count;
        return next;
    }

    hidden(prev, {payload}) {
        const next = {...prev};
        next.statuses.hidden.statuses = payload.data.nested.counts;
        next.statuses.hidden.total = payload.data.count;
        return next;
    }

    getList(prev, {payload}) {
        const next = {...prev};
        next.feedback = payload.data;
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
            .r(FeedbackListActions.loadFeedbackList, this.getList)
            .r(peopleActions.loadPeople, this.getFeedbackAuthor);
    }

}
