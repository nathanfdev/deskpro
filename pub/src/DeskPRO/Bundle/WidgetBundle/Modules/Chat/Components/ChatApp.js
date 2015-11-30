import React, { PropTypes } from 'react';
import { Link } from 'react-router';

export class ChatApp extends React.Component {

  static propTypes = {
    children: PropTypes.node
  };

  render() {
    const { children } = this.props;

    return (
      <div>
        Chat:<br />

        <Link to="/chat/begin/simple">/chat/begin/simple</Link><br />
        <Link to="/chat/begin/conversation">/chat/begin/conversation</Link><br />
        <Link to="/chat/begin/form">/chat/begin/form</Link><br />
        <Link to="/chat/active">/chat/active</Link><br />
        <Link to="/chat/done">/chat/done</Link><br />

        {children}
      </div>
    );
  }
}
