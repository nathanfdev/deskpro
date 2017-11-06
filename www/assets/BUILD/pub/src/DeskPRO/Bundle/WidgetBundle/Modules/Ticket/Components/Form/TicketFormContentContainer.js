import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import $ from 'jquery';
import { TicketFormContent } from './TicketFormContent';
import { TicketFormSpinner } from './TicketFormSpinner';
import { bootstrapTicketApp, saveNewTicketForm } from '../../Actions/ticketActions';
import { bootstrapSelector, contentSelector, contentLoadingSelector, contentSavingSelector } from '../../Selectors/ticket';
import { primaryColorSelector } from '../../../Application/Selectors/dpWindow';

@connect(state => ({
  content:      contentSelector(state),
  bootstrap:    bootstrapSelector(state),
  loading:      contentLoadingSelector(state),
  saving:       contentSavingSelector(state),
  primaryColor: primaryColorSelector(state)
}))
export default class TicketFormContentContainer extends React.Component {

  static propTypes = {
    dispatch:     PropTypes.func,
    content:      PropTypes.string,
    bootstrap:    PropTypes.bool,
    loading:      PropTypes.bool,
    saving:       PropTypes.bool,
    primaryColor: PropTypes.string
  };

  componentDidMount() {
    const { dispatch } = this.props;
    dispatch(bootstrapTicketApp());

    this.setPrimaryColor();
  }

  componentDidUpdate() {
    this.setPrimaryColor();
  }

  onSubmit = (data) => {
    this.props.dispatch(saveNewTicketForm(data));
  };

  setPrimaryColor() {
    const { primaryColor } = this.props;
    if (primaryColor) {
      $(this.formContainer).find('button').css({
        backgroundColor: primaryColor
      });
    }
  }

  render() {
    const { bootstrap, loading, saving, content } = this.props;
    const style = {};
    if (!$('.form-ticket', content).length) {
      style.display = 'none';
    }

    return bootstrap || loading
      ? <TicketFormSpinner />
      : <div ref={(c) => { this.formContainer = c; }}>
        <div className="header open-new-ticket" style={style}>
          <span className="img" />
          <h1>{portalPhrases.get('portal.widget.new-ticket-title')}</h1>
          <p>{portalPhrases.get('portal.tickets.new-intro')}</p>
        </div>
        <div className="dpdesignportal-form">
          <TicketFormContent
            content={content}
            saving={saving}
            onSubmit={this.onSubmit}
          />
        </div>
      </div>;
  }
}
