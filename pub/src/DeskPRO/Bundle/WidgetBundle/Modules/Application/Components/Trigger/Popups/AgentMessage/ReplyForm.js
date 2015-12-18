import React, { PropTypes } from 'react';

export class ReplyForm extends React.Component {

  static propTypes = {
    onClick: PropTypes.func
  };

  render() {
    return (
      <form onClick={this.props.onClick}>
        <input type="text" placeholder="Reply" />
        <button><i className="fa fa-angle-double-right"></i></button>
      </form>
    );
  }
}
