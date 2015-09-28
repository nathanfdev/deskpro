import React from "react";
import AppContainer from "DeskPRO/Component/AppContainer";
import { NavContainer } from './Nav/NavContainer';
import { ListContainer } from './List/ListContainer';
import { IntlProvider } from 'react-intl';

export class FeedbackApp extends React.Component {

  render() {
    return (
      <AppContainer thisAppId="feedback">
        <NavContainer/>
        <IntlProvider locale={window.DP_LOCALE} messages={window.DP_LANG_MESSAGES}>
          <ListContainer/>
        </IntlProvider>
      </AppContainer>
    );
  }
}
