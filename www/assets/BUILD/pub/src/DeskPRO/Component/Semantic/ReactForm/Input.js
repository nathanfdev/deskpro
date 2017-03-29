import React, { PropTypes } from 'react';

class Input extends React.Component {

  static propTypes = {
    onChange: PropTypes.func
  };

  onChange = (event) => {
    this.props.onChange(event.currentTarget.value || '');
  };

  render() {
    return <input {...this.props} onChange={this.onChange} />;
  }
}

export default Input;
