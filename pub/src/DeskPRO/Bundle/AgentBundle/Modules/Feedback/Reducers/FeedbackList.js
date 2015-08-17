import * as FeedbackListActions from "../Actions/FeedbackListActions.js";
import { Reducer } from "Ampliflux/reducers";

export default class FeedbackList extends Reducer {
    getInitialState() {
        return {
            toValidate: 0,
            commentsToReview: 0
        };
    }

    toValidate(state, action) {
        return {
            ...state,
            toValidate: action.payload.data.count
        };
    }

    commentsToReview(state, action) {
        return {
            ...state,
            commentsToReview: action.payload.data.count
        };
    }

    registerHandlers() {
        this
            .r(FeedbackListActions.feedbackToValidate, this.toValidate)
            .r(FeedbackListActions.commentsToReview, this.commentsToReview);
    }

}
