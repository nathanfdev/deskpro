import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { hasErrors, FieldErrors } from 'DeskPRO/Component/Form/FormErrors';

export class UserInfoForm extends React.Component {

  static propTypes = {
    title:        PropTypes.string.isRequired,
    field:        PropTypes.string,
    isSubmit:     PropTypes.bool,
    onSubmit:     PropTypes.func.isRequired,
    children:     PropTypes.any, // eslint-disable-line react/forbid-prop-types
    errors:       PropTypes.object,
    required:     PropTypes.bool,
    primaryColor: PropTypes.string
  };

  onSubmit = (event) => {
    event.preventDefault();
    this.props.onSubmit();
  };

  render() {
    const { field, title, children, errors, isSubmit, required, primaryColor } = this.props;
    const buttonStyles = {};
    if (primaryColor) {
      buttonStyles.backgroundColor = primaryColor;
    }

    return (
      <div className={classNames('dpdesignportal-collect-user-info', { error: hasErrors(errors, field) })}>
        <span className="title">{title}{required ? ' *' : ''}</span>
        <form onSubmit={this.onSubmit}>
          {children}
          <FieldErrors errors={errors} name={field} />
          {isSubmit
            ? <div className="spinner"><i /></div>
            : <button style={buttonStyles}>Go</button>
          }
        </form>
      </div>
    );
  }
}
