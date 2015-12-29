import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { TicketFormContent } from './TicketFormContent';
import { TicketFormSpinner } from './TicketFormSpinner';
import { loadForm } from '../../Actions/ticketActions';
import { contentSelector, contentLoadingSelector } from '../../Selectors/ticket';

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
    dispatch(loadForm());
  }

  render() {
    const { loading, content } = this.props;
    return loading ? <TicketFormSpinner /> : <TicketFormContent content={content} />;
  }
}
