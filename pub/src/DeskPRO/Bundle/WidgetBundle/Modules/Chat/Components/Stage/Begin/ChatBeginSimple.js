import React, { PropTypes } from 'react';
import { WaitingPreview } from '../WaitingPreview';

export class ChatBeginSimple extends React.Component {

  static propTypes = {
    onSubmit: PropTypes.func
  };

  componentDidMount() {
    this.props.onSubmit();
  }

  render() {
    return <WaitingPreview />;
  }
}
