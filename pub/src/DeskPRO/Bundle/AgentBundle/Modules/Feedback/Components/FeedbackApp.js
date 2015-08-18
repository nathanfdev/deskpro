import React from "react";
import { connect } from 'redux/react';
import AppContainer from "DeskPRO/Component/AppContainer";
import FeedbackNavFrame from "./FeedbackNavFrame";
import FeedbackListFrame from "./FeedbackListFrame";

@connect(state => ({
    user: state.user,
    dp_window: state.dp_window
}))
export default class FeedbackApp extends React.Component {
  render() {
    return (
      <AppContainer thisAppId="feedback" {...this.props}>
        <FeedbackNavFrame {...this.props} />
        <FeedbackListFrame {...this.props} />
      </AppContainer>
    );
  }
}
