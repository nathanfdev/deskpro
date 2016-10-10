import React, { PropTypes } from 'react';

class Form extends React.Component {

  static propTypes = {
    formValue: PropTypes.object,
    children:  PropTypes.any
  };

  renderChild(child, key) {
    const childProps = { ...child.props, key };
    const { formValue } = this.props;

    if (formValue) {
      childProps.formValue = formValue;
    }

    return React.cloneElement(child, childProps);
  }

  render() {
    const { children } = this.props;

    return (
      <form className="ui form" {...this.props}>
        {Array.isArray(children) ? children.map((child, i) => this.renderChild(child, i)) : this.renderChild(children)}
      </form>
    );
  }
}

export default Form;
