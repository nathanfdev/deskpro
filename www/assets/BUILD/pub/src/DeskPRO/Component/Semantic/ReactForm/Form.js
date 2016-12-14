import React, { PropTypes } from 'react';
import { getFormDataErrors } from 'DeskPRO/Component/Form/FormErrors';

class Form extends React.Component {

  static propTypes = {
    formValue: PropTypes.object,
    children:  PropTypes.oneOfType([PropTypes.array, PropTypes.node])
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
    const { children, formValue } = this.props;

    return (
      <form className="ui form" {...this.props}>
        {Array.isArray(children) ? children.map((child, i) => this.renderChild(child, i)) : this.renderChild(children)}
        {getFormDataErrors(formValue) &&
          <div className="ui negative message">
            <ul>
              {getFormDataErrors(formValue).map((error, index) =>
                <li key={index}>{error.message}</li>
              )}
            </ul>
          </div>}
      </form>
    );
  }
}

export default Form;
