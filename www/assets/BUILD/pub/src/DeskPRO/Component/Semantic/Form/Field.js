import PropTypes from 'prop-types';
import React from 'react';
import { hasErrors, getLastError } from 'DeskPRO/Component/Form/FormErrors';
import classNames from 'classnames';

class Field extends React.Component {
  static propTypes = {
    children:  PropTypes.node,
    field:     PropTypes.string,
    errors:    PropTypes.object,
    className: PropTypes.string
  };

  getError = () => {
    const { errors, field } = this.props;
    if (hasErrors(errors, field)) {
      return <div className="error-message">{getLastError(errors, field)}</div>;
    }
    return null;
  };

  render() {
    const { children, className, errors, field } = this.props;
    return (
      <div className={classNames('field', className, { error: hasErrors(errors, field) })}>
        {children}
        {this.getError()}
      </div>
    );
  }
}
export default Field;
