import React, { PropTypes } from 'react';
import { FieldWrapper } from './FieldWrapper';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned';

export class Password extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  render() {
    const { value, onChange } = this.props;

    return (
      <FieldWrapper iconClass="fa-lock" label="Password" errorMessage="Wrong password">
        <Positioned
          isOpen={true}
          positionTarget={this}
          positionAt="left top"
          positionMy="right center">

          <div className="dpw-login-form-warning-container warning-container">
            <i className="fa fa-arrow-circle-o-up"></i> <span>Looks like caps lock is on?</span>
          </div>
        </Positioned>

        <input type="password" placeholder="........." value={value} onChange={onChange} />
      </FieldWrapper>
    );
  }
}
