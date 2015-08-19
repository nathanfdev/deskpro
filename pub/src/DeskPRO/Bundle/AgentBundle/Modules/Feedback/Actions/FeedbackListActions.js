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
    (trigger) => {

    }
);