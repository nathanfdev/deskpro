import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { TicketFormContent } from './TicketFormContent';
import { TicketFormSpinner } from './TicketFormSpinner';
import { loadNewTicketForm, saveNewTicketForm } from '../../Actions/ticketActions';
import { contentSelector, contentLoadingSelector, contentSavingSelector } from '../../Selectors/ticket';
import history from '../../../../Services/history';

@connect(state => ({
  content: contentSelector(state),
  loading: contentLoadingSelector(state),
  saving: contentSavingSelector(state)
}))
export class TicketFormContentContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    content: PropTypes.string,
    loading: PropTypes.bool,
    saving: PropTypes.bool
  };

  componentDidMount() {
    const { dispatch } = this.props;
    dispatch(loadNewTicketForm());
  }

  onSubmit = data => {
    const { dispatch } = this.props;
    const promise = dispatch(saveNewTicketForm(data));
    promise.then(() => history.replace('ticket/form_submitted'));
  };

  render() {
    const { loading, saving, content } = this.props;
    return loading
      ? <TicketFormSpinner />
      : <TicketFormContent content={content}
                           saving={saving}
                           onSubmit={this.onSubmit} />;
  }
}
