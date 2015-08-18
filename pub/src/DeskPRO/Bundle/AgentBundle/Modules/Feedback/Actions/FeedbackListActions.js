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

export const loadFeedbackList = createAction(
    "FEEDBACK_LIST",
    (trigger) => {

    }
);