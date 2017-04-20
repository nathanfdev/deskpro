import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import $ from 'jquery';
import { TicketFormContent } from './TicketFormContent';
import { TicketFormSpinner } from './TicketFormSpinner';
import { bootstrapTicketApp, saveNewTicketForm } from '../../Actions/ticketActions';
import { bootstrapSelector, contentSelector, contentLoadingSelector, contentSavingSelector } from '../../Selectors/ticket';

@connect(state => ({
  content:   contentSelector(state),
  bootstrap: bootstrapSelector(state),
  loading:   contentLoadingSelector(state),
  saving:    contentSavingSelector(state)
}))
export default class TicketFormContentContainer extends React.Component {

  static propTypes = {
    dispatch:  PropTypes.func,
    content:   PropTypes.string,
    bootstrap: PropTypes.bool,
    loading:   PropTypes.bool,
    saving:    PropTypes.bool
  };

  componentDidMount() {
    const { dispatch } = this.props;
    dispatch(bootstrapTicketApp());
  }

  onSubmit = (data) => {
    this.props.dispatch(saveNewTicketForm(data));
  };

  render() {
    const { bootstrap, loading, saving, content } = this.props;
    const style = {};
    if (!$('.form-ticket', content).length) {
      style.display = 'none';
    }
    return bootstrap || loading ? <TicketFormSpinner /> :
      <div>
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
