import PropTypes from 'prop-types';
import React from 'react';

class Textarea extends React.Component {

  static propTypes = {
    onChange: PropTypes.func
  };

  onChange = (event) => {
    this.props.onChange(event.currentTarget.value || '');
  };

  render() {
    return <textarea {...this.props} onChange={this.onChange} />;
  }
}

export default Textarea;
