import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { TicketFormContent } from './TicketFormContent';
import { TicketFormSpinner } from './TicketFormSpinner';
import { loadNewTicketForm, saveNewTicketForm } from '../../Actions/ticketActions';
import { contentSelector, contentLoadingSelector } from '../../Selectors/ticket';
import history from '../../../../Services/history';

@connect(state => ({
  content: contentSelector(state),
  loading: contentLoadingSelector(state)
}))
export class TicketFormContentContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    content: PropTypes.string,
    loading: PropTypes.bool
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
    const { loading, content } = this.props;
    return loading
      ? <TicketFormSpinner />
      : <TicketFormContent content={content} onSubmit={this.onSubmit} />;
  }
}
