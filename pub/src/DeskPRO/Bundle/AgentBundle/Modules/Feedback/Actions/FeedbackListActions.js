import { createAction } from "Ampliflux/actions";
import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import * as Feedback from "DeskPRO/Bundle/AgentBundle/Services/Api/Feedback";

export const bogusAction = createAction(
    "FEEDBACK_BOGUS",
    (trigger) => {
        setTimeout(function () {
            trigger(true);
        }, 500);
    }
);

export const feedbackToValidate = createAction(
    "FEEDBACK_TO_VALIDATE",
    (trigger) => {
        Feedback.toValidate().then(
            (value) => trigger(value.getData())
        );
    }
);