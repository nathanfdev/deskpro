import React, { PropTypes } from 'react';

export class ReplyForm extends React.Component {

  static propTypes = {
    backgroundColor: PropTypes.string,
    textColor: PropTypes.string,
    onClick: PropTypes.func
  };

  render() {
    const { backgroundColor, textColor } = this.props;

    return (
      <form onClick={this.props.onClick}>
        <input type="text" placeholder="Reply" />
        <button style={{
          backgroundColor: backgroundColor,
          color: textColor
        }}>
          <i className="fa fa-angle-double-right" />
        </button>
      </form>
    );
  }
}
