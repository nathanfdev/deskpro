import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { loadForm } from '../../Actions/ticketActions';
import { contentSelector } from '../../Selectors/ticket';

@connect(state => ({
  content: contentSelector(state)
}))
export class TicketFormLoaderContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    content: PropTypes.string
  };

  componentDidMount() {
    const { dispatch } = this.props;
    dispatch(loadForm());
  }

  render() {
    return (
      <div dangerouslySetInnerHTML={{__html: this.props.content}} />
    );
  }
}
