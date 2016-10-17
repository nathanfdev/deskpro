import React from 'react';
import { TicketFormContentContainer } from './TicketFormContentContainer';
import { WidgetBodyScrollAreaContainer } from '../../../Application/Components/Widget/Parts/Body/WidgetBodyScrollAreaContainer';

export default class TicketForm extends React.Component {

  render() {
    return (
      <WidgetBodyScrollAreaContainer>
        <div className="dpdesignportal-content dpdesignportal-open-new-ticket">
          <TicketFormContentContainer />
        </div>
      </WidgetBodyScrollAreaContainer>
    );
  }
}
