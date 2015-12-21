import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { windowResize } from '../../../../Application/Actions/dpWindowActions';

@connect()
export class ChatContentContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    children: PropTypes.any
  };

  componentDidUpdate() {
    this.props.dispatch(windowResize());
  }

  render() {
    return (
      <div className="dpdesignportal-chat-footer">
        {this.props.children}
      </div>
    );
  }
}
