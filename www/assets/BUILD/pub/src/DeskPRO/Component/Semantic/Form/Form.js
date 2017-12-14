import PropTypes from 'prop-types';
import React from 'react';

class Form extends React.Component {
  static propTypes = {
    children: PropTypes.node,
    onSubmit: PropTypes.func
  };
  static defaultProps = {
    onSubmit() {}
  };

  handleSubmit = (e) => {
    e.preventDefault();
    this.props.onSubmit();
  };

  render() {
    return (
      <form className="ui form" onSubmit={this.handleSubmit}>
        {this.props.children}
      </form>
    );
  }
}
export default Form;
