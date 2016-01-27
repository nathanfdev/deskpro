import React, { Component, PropTypes } from 'react';
import { LabelsDictionary } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

export class LabelsTab extends Component {
  static propTypes = {
    labels: PropTypes.object.isRequired
  };

  render() {
    return <LabelsDictionary labels={this.props.labels} />;
  }
}
