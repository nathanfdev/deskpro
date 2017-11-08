import PropTypes from 'prop-types';
import React from 'react';

class Form extends React.Component {
  static propTypes = {
    onSubmit: PropTypes.func,
    children: PropTypes.node
  };
  static defaultProps = {
    onSubmit() {}
  };

  render() {
    return (
      <form className="ui form" onSubmit={this.props.onSubmit}>
        {this.props.children}
      </form>
    );
  }
}
export default Form;
