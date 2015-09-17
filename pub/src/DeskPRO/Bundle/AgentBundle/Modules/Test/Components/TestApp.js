import React from "react";
import { connect } from 'react-redux';
import AppContainer from "DeskPRO/Component/AppContainer";

import TestNavFrame from "./TestNavFrame";

export default class TestApp extends React.Component {
  render() {
    return (
      <AppContainer thisAppId="test">
        <TestNavFrame />
      </AppContainer>
    );
  }
}
