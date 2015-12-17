import React from 'react';
import { Router, Redirect } from 'react-router';
import { WidgetFrameContainer } from './WidgetFrameContainer';
import { Widget } from './Parts/Widget';
import { WidgetHeaderContainer } from './Parts/Header/WidgetHeaderContainer';
import { WidgetBody } from './Parts/WidgetBody';
import { WidgetFooter } from './Parts/WidgetFooter';
import { ChatRouter } from '../../Chat/Components/ChatRouter';
import history from '../../../Services/history';

export class WidgetApp extends React.Component {

  componentDidMount() {
    window.widgetFrame = parent.window.widget_iframe;
  }

  render() {
    return (
      <WidgetFrameContainer>
        <Widget>
          <WidgetHeaderContainer />
          <WidgetBody>
            <Router history={history}>
              <Redirect from="/" to="chat"/>
              <ChatRouter />
            </Router>
          </WidgetBody>
          <WidgetFooter />
        </Widget>
      </WidgetFrameContainer>
    );
  }
}