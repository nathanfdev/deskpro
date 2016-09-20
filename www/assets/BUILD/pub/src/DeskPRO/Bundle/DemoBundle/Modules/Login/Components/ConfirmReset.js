import React, { PropTypes } from 'react';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import { Button } from 'DeskPRO/Component/Semantic/Button';

export class ConfirmResetContainer extends React.Component {
  static contextTypes = {
    router: PropTypes.object.isRequired
  };

  onCancelButton = () => {
    this.context.router.push('/confirm-extend');
  }

  onReset = () => {
  }

  render() {
    return (
      <ConfirmReset
        onCancelButton={this.onCancelButton}
        onReset={this.onReset}
      />
    );
  }
}
export class ConfirmReset extends React.Component {
  static propTypes = {
    onCancelButton: PropTypes.func,
    onReset:        PropTypes.func
  };

  render() {
    return (
      <Segment className="confirm-reset">
        <h3>Confirm trial reset</h3>
        <p>
          This will erase all your data and you'll be starting a fresh trial.
        </p>
        <Button onClick={this.props.onReset}>Reset trial</Button><br />
        <Button onClick={this.props.onCancelButton} className="basic">Cancel</Button>
      </Segment>
    );
  }
}
