import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { WaitingPreview } from './WaitingPreview';
import { agentAcceptTimeoutSelector } from '../../../../Application/Selectors/dpWindow';
import { TicketFormButton } from './TicketFormButton';

@connect(state => ({
  acceptTimeout: agentAcceptTimeoutSelector(state)
}))
export class ChatWaitingContainer extends React.Component {

  static propTypes = {
    acceptTimeout: PropTypes.number
  };

  constructor(props) {
    super(props);
    this.state = {
      buttonShown: false
    };
  }

  componentDidMount() {
    this.timeout = setTimeout(this.onShowButton, this.props.acceptTimeout * 1000);
  }

  componentWillUnmount() {
    clearTimeout(this.timeout);
  }

  onShowButton = () => {
    this.setState({
      buttonShown: true
    });
  };

  render() {
    return (
      <div>
        <WaitingPreview />
        {this.state.buttonShown && <TicketFormButton />}
      </div>
    );
  }
}
