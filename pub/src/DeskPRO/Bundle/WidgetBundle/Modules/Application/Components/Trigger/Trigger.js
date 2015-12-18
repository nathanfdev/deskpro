import React from 'react';
import { TriggerFrameContainer } from './TriggerFrameContainer';
import { WidgetOpenContainer } from './WidgetOpenContainer';
import { HelpButtonContainer } from './Button/HelpButtonContainer';

export class Trigger extends React.Component {

  componentDidMount() {
    window.triggerFrame = parent.window.widget_trigger_iframe;
  }

  render() {
    return (
      <TriggerFrameContainer>
        <WidgetOpenContainer>
          <HelpButtonContainer />
        </WidgetOpenContainer>
      </TriggerFrameContainer>
    );
  }
}
