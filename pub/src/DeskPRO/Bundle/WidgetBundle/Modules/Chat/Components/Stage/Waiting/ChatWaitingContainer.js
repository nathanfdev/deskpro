import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { WaitingPreview } from './WaitingPreview';
import { agentAcceptTimeoutSelector } from '../../../../Application/Selectors/dpWindow';
import history from '../../../../../Services/history';

@connect(state => ({
  acceptTimeout: agentAcceptTimeoutSelector(state)
}))
export class ChatWaitingContainer extends React.Component {

  static propTypes = {
    acceptTimeout: PropTypes.number
  };

  componentDidMount() {
    this.timeout = setTimeout(() => history.replace('/ticket/form'), this.props.acceptTimeout * 1000);
  }

  componentWillUnmount() {
    clearTimeout(this.timeout);
  }

  render() {
    return <WaitingPreview />;
  }
}
