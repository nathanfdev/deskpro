import React, { PropTypes } from 'react';

class Form extends React.Component {
  static propTypes = {
    children: PropTypes.node
  };

  render() {
    return (
      <form className="ui form">
        {this.props.children}
      </form>
    );
  }
}
export default Form;
