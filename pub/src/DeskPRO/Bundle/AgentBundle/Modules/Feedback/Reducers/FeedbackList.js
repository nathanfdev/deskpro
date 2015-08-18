import * as FeedbackListActions from "../Actions/FeedbackListActions.js";
import { Reducer } from "Ampliflux/reducers";

export default class FeedbackList extends Reducer {
    getInitialState() {
        return {
            toValidateCount: 0,
            commentsToReviewCount: 0,
            labels: [],
            types: []
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

    registerHandlers() {
        this
            .r(FeedbackListActions.feedbackToValidate, this.toValidate)
            .r(FeedbackListActions.feedbackLabels, this.labels)
            .r(FeedbackListActions.feedbackTypes, this.types)
            .r(FeedbackListActions.commentsToReview, this.commentsToReview);
    }

}
