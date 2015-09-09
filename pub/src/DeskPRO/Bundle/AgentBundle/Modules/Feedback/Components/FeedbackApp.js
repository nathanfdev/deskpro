import React from "react";
import AppContainer from "DeskPRO/Component/AppContainer";
import { Nav } from './Nav/Nav';
import { ListContainer } from './List/ListContainer';

export class FeedbackApp extends React.Component {

  render() {
    return (
      <AppContainer thisAppId="feedback">
        <Nav/>
        <ListContainer/>
      </AppContainer>
    );
  }
}
