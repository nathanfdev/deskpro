import React, { PropTypes } from 'react';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';

export class ExtendTrialContainer extends React.Component {
  render() {
    return <ExtendTrial />;
  }
}

export class ExtendTrial extends React.Component {
  static propTypes = {
    onSubmit: PropTypes.func
  };

  render() {
    return (
      <Segment />
    );
  }
}
