import React from 'react';
import { TriggerFrameContainer } from './TriggerFrameContainer';
import { WidgetOpenContainer } from './WidgetOpenContainer';
import { HelpButton } from './Button/HelpButton';

export class TriggerApp extends React.Component {

  render() {
    return (
      <TriggerFrameContainer>
        <WidgetOpenContainer>
          <HelpButton />
        </WidgetOpenContainer>
      </TriggerFrameContainer>
    );
  }
}
