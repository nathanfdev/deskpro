import { Reducer } from "Ampliflux/reducers";

export default class FeedbackList extends Reducer {
    getInitialState() {
        return {
            FeedbackList: [],
        };
    }

    registerHandlers() {this
        .r("FEEDBACK_LOAD_FEEDBACK", this.setPayload('FeedbackList', 'data'))
    }
}
