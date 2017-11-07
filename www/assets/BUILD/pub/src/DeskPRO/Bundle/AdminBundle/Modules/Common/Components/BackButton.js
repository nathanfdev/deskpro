import PropTypes from 'prop-types';
import React from 'react';

class BackButton extends React.Component {

  static propTypes = {
    onClick: PropTypes.func.isRequired
  };

  render() {
    return (
      <button className="ui left floated basic button section-header-button" onClick={this.props.onClick}>
        <i className="triangle left icon" />
        Back
      </button>
    );
  }
}

export default BackButton;
