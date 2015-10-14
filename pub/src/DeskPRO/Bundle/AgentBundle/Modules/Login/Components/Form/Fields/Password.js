import React, { PropTypes } from 'react';
import { FieldWrapper } from './FieldWrapper';
import jQuery from 'jquery';

export class Password extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  componentDidMount() {
    const $warningContainer = jQuery('.warning-container');
    if ($warningContainer) {
      const warningWidth = (parseInt($warningContainer.css('width').replace(/px/, ''), 10) * -1 + 5) + 'px';
      $warningContainer.css('left', warningWidth);
    }
  }

  render() {
    const { value, onChange } = this.props;

    return (
      <FieldWrapper iconClass="fa-lock" label="Password" errorMessage="Wrong password">
        <div className="dpw-login-form-warning-container warning-container">
          <i className="fa fa-arrow-circle-o-up"></i> <span>Looks like caps lock is on?</span>
        </div>

        <input type="password" placeholder="........." value={value} onChange={onChange} />
      </FieldWrapper>
    );
  }
}
